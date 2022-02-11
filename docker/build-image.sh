#!/bin/bash -e

cd "$(dirname "$0")"

datahubFile=../dist/datahub.tar.gz
datahubFilePath=$(realpath $datahubFile)
targetDir=html

if [ ! -f $datahubFilePath ]; then
    echo "File $datahubFile is missing."
    echo "Please run ../build.sh first"
    exit 1
fi

rm -rf $targetDir
mkdir $targetDir
cd $targetDir
tar xzf $datahubFilePath
cd ..

docker build -t datahub .

rm -r $targetDir

echo ""
echo "Done, created new docker image called 'datahub'"
echo "Now you've probably want to do something like:"
echo "   docker run -d --name datahub -p 8080:80 -v datahub:/var/www/html/api/data datahub"
