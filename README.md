# Bulk Boltcards

![Example Output](https://github.com/leesalminen/bulk-boltcards/raw/main/img/example_output.png "example output")

## To run

First, install pre-requisites:

Debian 10

```
sudo apt install chromium curl php php-gmp php-mbstring php-gd php-curl libcurl4-openssl-dev git
```

Ubuntu
If you need to downgrade from php 8.1 to 7.4 follow this [tutorial](https://thecodebeast.com/downgrade-php-8-0-to-7-4-ubuntu-digital-ocean/) and install this extra dependencies later


```
sudo apt install curl php php-gmp php-mbstring php-gd php-curl libcurl4-openssl-dev git
```

```
sudo apt-get install php7.4-gmp 
sudo apt-get install php7.4-bcmath
sudo apt-get install php7.4-curl

```




Then, clone this repository.

```
git clone https://github.com/leesalminen/bulk-boltcards.git
cd bulk-boltcards/
chmod 755 run.sh
```

Second, open `constants.php` and set your LNBits domain name in `DOMAIN_NAME`. You can customize the other available settings here, too. Each field is documented inline.

Then, run `./run.sh {mac|linux} {card_uid}`. Ex: `./run.sh linux 12345678900070`. The script will run and return an error message in the terminal, or will open Chrome/Chromium with the template & injected data. Using this method, the generated data including all keys is ephemeral. As soon as you close the browser tab, that data is gone and cannot be recovered.

Another option is to run `./run.sh {pdf|html} {card_uid}`. Ex: `./run.sh pdf 12345678900070 > wallet.pdf`. The script will run and return an error message in the terminal, or will send pdf or html to stdout which you can then use to save to a file on disk. Using this method, the generated data including all keys is saved to disk. This could pose a security risk if you aren't careful with your files. You should consider deleting these files from disk after printing them.

Print to A4 size paper on Landscape if no margins if supported by your printer. 