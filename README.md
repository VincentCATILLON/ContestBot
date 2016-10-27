Vote bot
==============

Adds votes to a contest anonymously (using Tor)

## Dependencies

```bash
sudo apt-get install tor php5
````

## Installation

Edit the following file: `/etc/tor/torrc` and

- Uncomment the directive:

```bash
ControlPort 9051
```

- Set:

```bash
CookieAuthentication 0
```

## Usage

To start the bot, for example (1234 is the targeted id to increment):

```bash
chmod +x loop.sh
./loop.sh 1234
```