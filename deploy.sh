#!/bin/bash

APP_ENV=prod

function set_acl() {
    # set ACL on var folder if necessary
    CURRENT_USER=`whoami`
    FOLDER="$1"
    ACL=`getfacl "$FOLDER" | grep -E "($CURRENT_USER|www-data):..x$" -ic`
    if [[ "$ACL" != 4 ]]; then
        echo "setting missing ACLs on $FOLDER"
        setfacl -R -m u:www-data:rwX -m u:"$CURRENT_USER":rwX "$FOLDER" && \
        setfacl -dR -m u:www-data:rwX -m u:"$CURRENT_USER":rwX "$FOLDER"
    fi
}
set_acl var

composer --ansi -n install --no-dev --optimize-autoloader
bin/console --ansi -n cache:clear --no-warmup
bin/console --ansi -n doctrine:schema:update --force
bin/console --ansi -n cache:warmup
