# Share your Tesla's location via Tessie and Home Assistant

🇩🇪 [Deutsche Version → README.md](README.md)

Some time ago I "experienced" a fantastic road trip. It would have been nice to share my location – without my phone turning into a hand warmer.

Since I use (and love) Tessie, like a lot of Tesla drivers, the idea was obvious: use its data to share the position instead of relying on the phone's battery-hungry Google Maps sharing. All I need is a web page that shows my Tesla's location *temporarily* and switches it off again when a timer runs out. Tessie doesn't offer this out of the box (at the moment), and since I run Home Assistant with the Tessie integration anyway, I simply turned the whole thing around.

The long story (in German) is on my blog: [Standort meines Tesla via Tessie und Home Assistant teilen](https://www.johnlose.de/2026/09/standort-meines-tesla-via-tessie-und-home-assistant-teilen/). This repo contains the code, so you can copy, read and improve it properly.

## What it looks like

Controls in the Home Assistant dashboard (dropdown with the sharing duration in minutes, timer, share buttons):

<img src="docs/tessieshare.jpeg" alt="Home Assistant dashboard: location sharing dropdown, timer, share via mail and SMS" width="320">

## How it works

```
Tesla ──► Tessie ──► Home Assistant ──POST (JSON + secret)──► update.php ──► data.json
                                                                               ▲
                                       Friends & family: index.html (Leaflet map) ─┘ polls every 5 s
```

- **Home Assistant** starts a timer via a dropdown and, while it runs, pushes the GPS data to your web space with a `rest_command` on every position change.
- **`update.php`** checks the secret, sanitises the values and writes them to `data.json`.
- **`index.html`** shows the position on a map (Leaflet / OpenStreetMap), plus destination, minutes until arrival and battery level on arrival.
- When the timer expires (or you set the dropdown to "Aus" / off), `expired` is sent and the map disappears for everyone immediately.

## Requirements

- Home Assistant with the **Tessie integration** (or any other Tesla integration that provides location, destination and arrival time)
- Your own **web space with PHP** – a basic shared hosting plan is plenty

## Setup

### 1. Web space

1. Create a folder whose URL you want to share later, e.g. `https://YOUR-WEBSITE.com/shareloc/`.
2. Upload the contents of [`webspace/`](webspace/): `index.html`, `update.php`, `data.json`.
3. Copy `config.example.php` to `config.php` and enter a **strong, very long secret**. This file must not go into Git (it is listed in `.gitignore`).
4. The web server must be able to write `data.json`.

### 2. Home Assistant

1. [`homeassistant/configuration.yaml`](homeassistant/configuration.yaml): dropdown, timer and `rest_command`. Replace `DEINE-WEBSITE.de`, the secret and the entity names (`deintesla_*`) with yours.
2. [`homeassistant/automations.yaml`](homeassistant/automations.yaml): the two automations (timer control and GPS push while driving).
3. Optional [`homeassistant/dashboard-card.yaml`](homeassistant/dashboard-card.yaml): dashboard controls including a "share via SMS" button.
4. Check the configuration and reload it, or restart Home Assistant.

> **Note:** If your `configuration.yaml` already has a `rest_command:`, `input_select:` or `timer:` block, merge the entries into it. A second block with the same name overrides the first one – I tripped over this myself (`service_not_found`). The dropdown labels and notification texts are in German; adjust them as you like.

## Privacy and security

This thing exists to show *your* location – I assume that is what you want. I'm also definitely not free of mistakes. Choose a strong, very long secret that can't be brute-forced quickly. I built in what is reasonably possible on regular shared hosting (type sanitising of the input, `hash_equals`, `LOCK_EX`, escaping of displayed values). If you have suggestions for improvements, bring them on – issues and pull requests are welcome.

The page only shows data while a timer is running. Afterwards `data.json` contains just `{"status":"expired"}`.

## Credits

Map display by [Leaflet](https://leafletjs.com) (Volodymyr Agafonkin) and map data from [OpenStreetMap](https://www.openstreetmap.org/copyright). I use the original [Tessie integration](https://www.home-assistant.io/integrations/tessie/) for Home Assistant.

## License

[Apache License 2.0](LICENSE)
