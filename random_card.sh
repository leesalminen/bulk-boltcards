#! /bin/bash

echo -n "$(dd if=/dev/random bs=1 count=7 status=none|xxd -ps)"



