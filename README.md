# System biblioteczny (Symfony + Vue.js)

Proste API do zarządzania księgozbiorem biblioteki wraz z lekkim interfejsem
we Vue.js. Pracownik biblioteki może dodawać i usuwać książki, przeglądać cały
księgozbiór oraz zmieniać stan książki (wypożyczona / dostępna).

## Stos technologiczny

- PHP 8.3, Symfony 6.4
- PostgreSQL 16
- Doctrine ORM + Doctrine Migrations
- Symfony Messenger (rozdzielone szyny `command.bus` / `query.bus`, CQRS)
- Twig + Vue.js 3 (frontend)
- Docker / docker compose (Apache + PHP + PostgreSQL)

## Uruchomienie

```bash
docker compose up --build
```

Po zbudowaniu obrazu kontener aplikacji czeka na bazę danych, uruchamia migracje,
zasiewa kilka przykładowych książek i startuje serwer.

- Interfejs: http://localhost:8080
- API: http://localhost:8080/api/books

## Model danych

Encja `Book`:

| Pole                 | Opis                                                        |
|----------------------|-------------------------------------------------------------|
| `serialNumber`       | Unikalny, sześciocyfrowy numer seryjny wprowadzany ręcznie  |
| `title`              | Tytuł                                                       |
| `author`             | Autor                                                       |
| `borrowed`           | Czy książka jest obecnie wypożyczona                        |
| `borrowerCardNumber` | Sześciocyfrowy numer karty bibliotecznej wypożyczającego    |
| `borrowedAt`         | Data i godzina wypożyczenia                                 |

## Endpointy

| Metoda   | Ścieżka                  | Opis                                   |
|----------|--------------------------|----------------------------------------|
| `GET`    | `/api/books`             | Lista książek (paginacja + wyszukiwanie) |
| `POST`   | `/api/books`             | Dodanie nowej książki                  |
| `DELETE` | `/api/books/{id}`        | Usunięcie książki                      |
| `PATCH`  | `/api/books/{id}/status` | Zmiana stanu: wypożyczona / dostępna   |

Parametry zapytania dla `GET /api/books`:

| Parametr | Domyślnie | Opis                                                     |
|----------|-----------|----------------------------------------------------------|
| `search` | brak      | Filtr po tytule, autorze lub numerze seryjnym            |
| `page`   | `1`       | Numer strony                                             |
| `limit`  | `10`      | Liczba pozycji na stronę (1-100)                         |

Odpowiedź: `{ "items": [...], "total": N, "page": N, "pages": N, "limit": N }`.

### Przykłady

Dodanie książki:

```bash
curl -X POST http://localhost:8080/api/books \
  -H 'Content-Type: application/json' \
  -d '{"serialNumber":"123456","title":"Lalka","author":"Bolesław Prus"}'
```

Wypożyczenie:

```bash
curl -X PATCH http://localhost:8080/api/books/1/status \
  -H 'Content-Type: application/json' \
  -d '{"borrowed":true,"borrowerCardNumber":"654321"}'
```

Zwrot:

```bash
curl -X PATCH http://localhost:8080/api/books/1/status \
  -H 'Content-Type: application/json' \
  -d '{"borrowed":false}'
```

## Organizacja kodu

```
src/
├── Application/
│   ├── Command/           # AddBook, DeleteBook, UpdateBookStatus (komendy + handlery)
│   └── Query/             # GetBooks (zapytanie + handler)
├── Controller/            # Cienki kontroler API + kontroler frontu
├── Dto/                   # Obiekty żądań z walidacją
├── Entity/                # Book (logika domenowa: borrow / giveBack)
├── Exception/             # Wyjątki domenowe mapowane na kody HTTP
├── EventSubscriber/       # Formatowanie błędów API do JSON
└── Repository/            # Dostęp do danych
```

Kontrolery jedynie budują komendy/zapytania i przekazują je na odpowiednią szynę
Messengera. Logika zapisu i odczytu jest rozdzielona (CQRS), a reguły domenowe
(wypożyczenie/zwrot) żyją w encji.

## Testy

```bash
composer install
vendor/bin/phpunit
```
