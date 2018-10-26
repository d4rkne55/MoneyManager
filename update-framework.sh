# NOTE: exit the script when an error gets thrown
# not very useful here though, as some info should get displayed when sth. goes wrong
#set -e

repository="https://github.com/d4rkne55/microFramework.git"

# exit the script when the given branch (or tag) doesn't exist
# not the fastest but the only working way I found checking for branches and tags in the repository
if ! git ls-remote --exit-code $repository $1 1> /dev/null; then
    echo Branch \'$1\' does not exist. Aborting.

    exit 1;
fi

# clone the GitHub repository to a temporary update/ folder
git clone $repository update

cd update

# if called with parameter, switch to the given branch
if [ -n "$1" ]; then
    git checkout -q $1 2> /dev/null
fi

# remove files that shouldn't be updated
rm -rf .git Classes/Example.class.php css templates .gitignore .htaccess README.md update-framework.sh

# copy the config file if it doesn't exist already
cp -n config.yml .. 2> /dev/null
rm -f config.yml

# overwrite the project's (framework) files with the ones in the update/ folder
cp -R ./ ../

cd ..

# remove the temporary update/ folder
rm -rf update
