#!/usr/bin/env bash
PWD=$(pwd)
MYD=$(dirname $0)
cd $MYD/../www
cloc \
	--by-file-by-lang \
	--quiet \
	--exclude-ext=min.js,min.css,r.js \
	js/{actions/*/,skills/,bindingHandlers/,extenders/,viewModels/,}*.js \
	application/{models/{*/,},views/{*/,},controllers/}*.php \
	css/*.css
cd $PWD
