# Digital Visibility Index

## Code Conventions
Die Code Conventions für das Projekt sind in der Datei `.editorconfig` unabhänigig von der IDE dokumentiert. Für manche Editoren ist die Installation einer [Erweiterung](http://editorconfig.org/#download) notwendig.

## Lokale Entwicklungsumgebung
### Voraussetzungen
Die folgenden Komponenten müssen installiert sein:
- [Docker](https://www.docker.com/products/container-runtime)
- [Docker Compose](https://docs.docker.com/compose/install/#install-compose)

### Start
Die Entwicklungsumgebung kann durch die Ausführung des folgenden Bash-Kommando gestartet werden:
```
./bin/start
```

### Stop
Die Entwicklungsumgebung kann durch die Ausführung des folgenden Bash-Kommando gestoppt werden:
```
./bin/stop
```

### Digital Visibility Index CLI
Das Command Line Interface (CLI) von dem Digital Visibility Index kann durch die Ausführung des folgenden Bash-Kommando genutzt werden:
```
./bin/run-cli
```

### Weitere Bash-Skripte
#### Composer
Der PHP-Paketmanager [Composer](https://getcomposer.org/) kann mit dem folgenden Skript verwendet werden:
```
./bin/composer
```

#### PHP_CodeSniffer
Der [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) kann mit dem folgenden Skript gestartet werden:
```
./bin/run-phpcs
```

#### Tests
Die Tests auf Basis von [PHPUnit](https://github.com/sebastianbergmann/phpunit) können mit dem folgenden Skript gestartet werden:
```
./bin/run-phpunit
```
