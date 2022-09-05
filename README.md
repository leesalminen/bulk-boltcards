# Bulk Boltcards

## To run

First, install pre-requisites:

Debian 10

```
sudo apt install curl php php-gmp php-mbstring php-gd php-curl libcurl4-openssl-dev git
```

Then, clone this repository.

```
git clone https://github.com/leesalminen/bulk-boltcards.git
cd bulk-boltcards/
```

Second, open `constants.php` and set your LNBits domain name in `DOMAIN_NAME`. You can customize the other available settings here, too. Each field is documented inline.

Then, run `./run.sh {mac|linux} {card_uid}`. Ex: `./run.sh mac 00000000000000`.

The script will run and return an error message in the terminal, or will open Chrome/Chromium with the template & injected data.

Print to A4 size paper on Landscape if no margins if supported by your printer. 
