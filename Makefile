Pentru a rula aceste scripturi veti avea nevoie de doua terminale.
Intr-un terminal, porniti serverul cu comanda:
```bash
php -S http://localhost:8080 server.php
```

Puteti verifica daca serverul functioneaza accessand in browser [http://localhost:8080/permissions](http://localhost:8080/permissions).

In celalalt terminal, puteti verifica daca ati implementat corect functionalitatea
endpoint-ului `POST /permissions/check` prin rularea comenzii:
```bash
php test.php
```
