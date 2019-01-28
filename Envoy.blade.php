@setup
    require __DIR__.'/vendor/autoload.php';
    (new \Dotenv\Dotenv(__DIR__, '.env'))->load();

    $user = isset($user) ? $user : "root"; // Change this
    $server = isset($server) ? $server : "server-name.com"; // Change this
    $userAndServer = $user .'@'. $server;

    $vandorRepositoryName = "vendor/repo" // Change this
    $repository = "git@bitbucket.org:{$vandorRepositoryName}.git";
    $branch = isset($branch) ? $branch : "stable";

    $baseDir = "/home/project_name"; // Change this
    $releasesDir = "{$baseDir}/releases";
    $persistentDir = "{$baseDir}/persistent";
    $currentDir = "{$baseDir}/current";

    $newReleaseName = date('Ymd-His');
    $newReleaseDir = "{$releasesDir}/{$newReleaseName}";

    function logMessage($message) {
        return "echo '\033[32m" .$message. "\033[0m';\n";
    }
@endsetup

@servers(['local' => '127.0.0.1', 'remote' => $userAndServer])

@macro('deploy')
    startDeployment
    cloneRepository
    runComposer
    runYarn
    updateSymlinks
    optimizeInstallation
    backupDatabase
    migrateDatabase
    blessNewRelease
    cleanOldReleases
    finishDeploy
@endmacro

@macro('deploy-code')
    deployOnlyCode
    finishDeploy
@endmacro

@task('startDeployment', ['on' => 'local'])
    {{ logMessage("🏃  Starting deployment...") }}
@endtask

@task('cloneRepository', ['on' => 'remote'])
    {{ logMessage("🌀  Cloning repository...") }}
    [ -d {{ $releasesDir }} ] || mkdir {{ $releasesDir }};
    [ -d {{ $persistentDir }} ] || mkdir {{ $persistentDir }};
    [ -d {{ $persistentDir }}/media ] || mkdir {{ $persistentDir }}/media;
    [ -d {{ $persistentDir }}/storage ] || mkdir {{ $persistentDir }}/storage;
    cd {{ $releasesDir }};

    # Create the release dir
    mkdir {{ $newReleaseDir }};

    # Clone the repo
    git clone {{ $repository }} --branch={{ $branch }} --depth=1 {{ $newReleaseName }};

    # Configure sparse checkout
    cd {{ $newReleaseDir }}
    git config core.sparsecheckout true
    echo "*" > .git/info/sparse-checkout
    echo "!storage" >> .git/info/sparse-checkout
    echo "!public/build" >> .git/info/sparse-checkout
    git read-tree -mu HEAD

    # Mark release
    cd {{ $newReleaseDir }}
    echo "{{ $newReleaseName }}" > public/release-name.txt

    # Mark latest tag
    export GIT_TAG=`git describe --abbrev=0 --tags --always`;
    echo $GIT_TAG > public/version

    # Import the environment config
    cd {{ $newReleaseDir }};
    ln -nfs {{ $baseDir }}/.env .env;
@endtask

@task('runComposer', ['on' => 'remote'])
    {{ logMessage("🚚  Running Composer...") }}
    cd {{ $newReleaseDir }};
    composer install --prefer-dist --no-scripts --no-dev -o;
@endtask

@task('runYarn', ['on' => 'remote'])
    {{ logMessage("📦  Running Yarn...") }}
    cd {{ $newReleaseDir }};
    yarn config set ignore-engines true;
    yarn;

    {{ logMessage("🌅  Generating assets...") }}
    cd {{ $newReleaseDir }};
    yarn run prod --progress false;
@endtask

@task('updateSymlinks', ['on' => 'remote'])
    {{ logMessage("🔗  Updating symlinks to persistent data...") }}
    # Remove the storage directory and replace with persistent data
    rm -rf {{ $newReleaseDir }}/storage;
    cd {{ $newReleaseDir }};
    ln -nfs {{ $baseDir }}/persistent/storage storage;

    # Remove the public/media directory and replace with persistent data
    rm -rf {{ $newReleaseDir }}/public/media;
    cd {{ $newReleaseDir }};
    ln -nfs {{ $baseDir }}/persistent/media public/media;

    # Remove the public/storage directory and replace with persistent data
    rm -rf {{ $newReleaseDir }}/public/storage;
    cd {{ $newReleaseDir }};
    ln -nfs {{ $baseDir }}/persistent/storage/app/public public/storage;

    # Import the environment config
    cd {{ $newReleaseDir }};
    ln -nfs {{ $baseDir }}/.env .env;
@endtask

@task('optimizeInstallation', ['on' => 'remote'])
    {{ logMessage("✨  Optimizing installation...") }}
    cd {{ $newReleaseDir }};
    php artisan clear-compiled;
@endtask

@task('backupDatabase', ['on' => 'remote'])
    {{ logMessage("📀  Backing up database...") }}
    cd {{ $newReleaseDir }}
    php artisan backup:run;
@endtask

@task('migrateDatabase', ['on' => 'remote'])
    {{ logMessage("🙈  Migrating database...") }}
    cd {{ $newReleaseDir }};
    php artisan migrate --force;
@endtask

@task('blessNewRelease', ['on' => 'remote'])
    {{ logMessage("🙏  Blessing new release...") }}
    ln -nfs {{ $newReleaseDir }} {{ $currentDir }};
    cd {{ $newReleaseDir }};

    php artisan horizon:terminate;

    php artisan cache:clear;
    php artisan view:clear;

    php artisan view:cache;
    php artisan config:cache;

    sudo /etc/init.d/php7.2-fpm restart
@endtask

@task('cleanOldReleases', ['on' => 'remote'])
    {{ logMessage("🚾  Cleaning up old releases...") }}
    # Delete all but the 5 most recent.
    cd {{ $releasesDir }};
    ls -dt {{ $releasesDir }}/* | tail -n +6 | xargs -d "\n" rm -rf;
@endtask

@task('finishDeploy', ['on' => 'remote'])
    {{ logMessage("🐛  Notifying BugSnag") }}

    cd {{ $currentDir }};
    REVISION=`git log -n 1 --pretty=format:"%H"`;
    php artisan bugsnag:deploy --repository "https://bitbucket.org/{{ $vandorRepositoryName }}" --branch "{{ $branch }}" --revision REVISION;

    {{ logMessage("🚀  Application deployed!") }}
@endtask

@task('deployOnlyCode',['on' => 'remote'])
    {{ logMessage("💻  Deploying code changes...") }}
    cd {{ $currentDir }}
    git pull origin {{ $branch }};
    php artisan cache:clear;
    php artisan view:clear;

    php artisan config:cache;
    php artisan view:cache;

    php artisan horizon:terminate;
@endtask
