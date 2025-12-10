# Word Cloud Engine (WCE)

## Overzicht

De Word Cloud Engine (WCE) is een Symfony/PHP applicatie die teksten verwerkt en woorden telt, met ondersteuning voor:

- Blokkeren stopwoorden: Stopwoorden worden genegeerd, tenzij aangegeven bij preferred
- Verboden woorden: Woorden die niet in de resultaten mogen verschijnen
- Preferred woorden: Woorden die bij voorkeur worden meegenomen, dit overschrijft stopwoorden en verboden woorden.
- Unicode en cijfers
- Apostrophes en hyphens: Apostrophes en hyphens in woorden blijven intact
- Parallel verwerking voor grote teksten: is beschikbaar mits de host dit ondersteund

Het project bevat een simpele **API endpoint** voor het genereren van data onderliggend aan een word cloud en een uitgebreide **unit test suite**.

---

## Vereisten
# setup met docker
- Docker en Docker Compose: de app bevat een docker omgeving. Deze installeert alle vereisten en de PHP Parallel extensie 

# setup zonder docker
- PHP 8.4+
- Composer
- Symfony CLI (optioneel, voor lokale server)

## Optioneel
- PHP Parallel extensie: Als geen gebruik gemaakt wordt van de docker omgeving kan Parallel geinstalleerd worden om parallel verwerking in  te schakelen.

---

## Installatie

# Met Docker
1. Clone de repository:

```
git clone <repo-url> wce
cd wce
```

2. Kopieer compose-example.yaml naar compose.yaml
3. Pas compose.yaml aan als deze in voorbeeld staat niet voldoet.
4. Build de Docker image en start een Container

```
Docker compose up -d
```

5. Installeer dependencies

```
docker compose exec php-wce composer install
```

6. Start de Symfony server

```
docker compose exec php-wce composer serve
```

De API is dan beschikbaar (bij compose-example) als http://localhost:9480

7. Stop de server weer (indien gewenst) met

```
docker compose exec php-wce symfony server:stop
```


# Zonder Docker
1. Zorg voor een omgeving die voldoet aan de vereisten
2. Clone de repository:

```
git clone <repo-url> wce
cd wce
```

3. Serve naar de app root folder: /wce. Hier is de symfony installatie te vinden.
4. Installeer dependencies

```
composer install
```

6. Start de Symfony server

```
composer serve
```

De API is dan beschikbaar als http://localhost:8000

7. Stop de server weer (indien gewenst) met

```
symfony server:stop
```

## Endpoint
POST /wordcloudengine

Parameters (form-data):
text            string  De te verwerken tekst
maxWords        int     Maximum aantal woorden in de output
forbiddenWords  array   Lijst van verboden woorden
preferredWords  array   Lijst van voorkeurwoorden

# Voorbeeld Form-data
Key: text               Value: THE Fall the crush the pain the FALL THE FaLL
Key: maxWords           Value: 100
Key: forbiddenWords[]   Value: (optioneel, meerdere keys voor array)
Key: preferredWords[]   Value: (optioneel)

!Let op
Forbidden/Preferred woorden kunnen leeg blijven, maar voor arrays moet je [] of meerdere keys gebruiken.

## Tests
# Unit
```
docker compose exec php-wce vendor/bin/phpunit --testdox
```

Unit tests staan in tests/Unit/
Controller tests in tests/Controller/

# Postman voorbeelden
In /data/postman staat een bestand met Postman voorbeelden om de API endpoint aan te roepen. Laad deze in in Postman en ze werken out-of-the-box met de Docker versie van WCE.
```wce.postman_examples.json```

## Logging
Logs worden geschreven naar: var/log/dev.log, var/log/test.log of var/log/prod.log afhankelijk van het environment.

Er wordt gebruik gemaakt van een static logger gebruikt, o.a. om parallel processing te ondersteunen.
