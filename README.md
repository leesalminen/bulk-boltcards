# Bulk Boltcards

## To run

First, clone this repository locally.

Second, open `constants.php` and set your LNBits domain name in `DOMAIN_NAME`. You can customize the other available settings here, too. Each field is documented inline.

Then, run `./run.sh {mac|linux} {card_uid}`. Ex: `./run.sh mac 00000000000000`.

The script will run and return an error message, or open Chrome/Chromium with the template & injected data.

Print to A4 size paper.
