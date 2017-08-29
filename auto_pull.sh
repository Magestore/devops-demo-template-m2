#!/bin/bash

cd data/www

HAS_CHANGED=$( git status -s | wc -l )

if [ $HAS_CHANGED -gt 0 ]; then
  git add -A
  git commit -m "add from system"
  git pull
  git push
else
  git pull
fi

echo "Pull successfuly"
