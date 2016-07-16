#!/bin/bash

date=$(date "+%Y%m%d-%H%M%S")
echo "----------------------------------------------"
echo "-> Start: ${date}"
echo "----------------------------------------------"
echo "-> Git branch:"
git rev-parse --abbrev-ref HEAD
git pull
echo "----------------------------------------------"
echo "-> Gulp:"
gulp build
echo "----------------------------------------------"
echo "-> Yii build:"
php yii build ${date}
echo "----------------------------------------------"
echo "-> Done: $(date "+%Y%m%d-%H%M%S")"
echo "----------------------------------------------"