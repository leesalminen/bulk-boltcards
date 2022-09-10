#! /bin/bash

LOCK=/var/lock/bulk-boltcards.lock

if [ ! -f $LOCK ]
then 
  touch $LOCK
  chmod 666 $LOCK
fi

flock --timeout 100 $LOCK ./run_lock.sh $*

exit $?
