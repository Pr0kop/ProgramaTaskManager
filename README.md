# TaskManager

Aplikacja do zarządzania zadaniami zbudowana w Symfony 7 jako zadanie rekrutacyjne dla Programa Software House.

## Technologie

| Technologia | Wersja | Zastosowanie |
|---|---|---|
| PHP | 8.3 | Język backend |
| Symfony | 7.x | Framework |
| Doctrine ORM | 3.x | Warstwa bazy danych |
| MySQL | 8.0 | Baza danych |
| OverblogGraphQLBundle | 1.x | GraphQL API |
| Symfony Messenger | 7.x | Event Sourcing / kolejkowanie |
| PHPUnit | 11.x | Testy jednostkowe |
| Docker | - | Konteneryzacja |

## Architektura

Projekt oparty na **Domain-Driven Design (DDD)**:

```
src/
├── Domain/          # Logika biznesowa (encje, VO, interfejsy, strategie, eventy)
│   ├── Task/
│   │   ├── Entity/
│   │   ├── Enum/
│   │   ├── Event/
│   │   ├── EventStore/
│   │   ├── Factory/
│   │   ├── Repository/
│   │   ├── Strategy/
│   │   └── ValueObject/
│   └── User/
│       ├── Entity/
│       ├── Enum/
│       ├── Factory/
│       ├── Repository/
│       └── ValueObject/
├── Application/     # Komendy i handlery (use case'y)
│   ├── Task/
│   └── User/
└── Infrastructure/  # Implementacje (Doctrine, kontrolery, GraphQL, security)
    ├── GraphQL/
    ├── Task/
    ├── User/
    └── Web/
```

### Wzorce projektowe

- **Factory Pattern** — `UserFactory`, `TaskFactory` tworzą obiekty bez `new` w kodzie aplikacyjnym
- **Strategy Pattern** — `ToInProgressStrategy`, `ToDoneStrategy` obsługują przejścia statusów (ToDo→InProgress→Done)
- **Event Sourcing** — każda operacja na `Task` generuje zdarzenie zapisywane w `task_events`

---

## Uruchomienie

### Wymagania

- Docker
- Docker Compose

### Pierwsze uruchomienie

```bash
# Klonuj repozytorium
git clone https://github.com/Pr0kop/ProgramaTaskManager.git
cd ProgramaTaskManager

# Skopiuj zmienne środowiskowe
cp .env .env.local

# Zbuduj i uruchom
make setup
```

Komenda `make setup` wykonuje: `build → up → install → db-create → migrate`.

### Codzienne uruchomienie

```bash
make up      # Uruchom kontenery
make down    # Zatrzymaj kontenery
make shell   # Wejdź do kontenera PHP
make test    # Uruchom testy
make migrate # Uruchom migracje
```

### Adresy

| Serwis | Adres |
|---|---|
| Aplikacja | http://localhost:8080 |
| Panel webowy | http://localhost:8080/web/login |
| MySQL | localhost:3306 |

### Zmienne środowiskowe (`.env`)

```env
DATABASE_URL="mysql://app:app@mysql:3306/task_manager?serverVersion=8.0"
MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0
DEFAULT_USER_PASSWORD=password123
```

---

## Import danych

Po uruchomieniu zaimportuj użytkowników z JSONPlaceholder:

```bash
curl -X POST http://localhost:8080/api/users/import
```

Importuje 10 użytkowników. Pierwszy użytkownik (Bret / sincere@april.biz) otrzymuje rolę **Admin**, pozostali **Member**.

Domyślne hasło dla wszystkich: `password123`

---

## REST API

Base URL: `http://localhost:8080`

### Autoryzacja

Endpointy `/api/users/me` wymagają nagłówka:
```
Authorization: Bearer {token}
```

Token uzyskasz logując się przez `POST /api/login`.

### Użytkownicy

| Metoda | Endpoint | Opis |
|---|---|---|
| `POST` | `/api/users/import` | Import z JSONPlaceholder |
| `GET` | `/api/users` | Lista wszystkich użytkowników |
| `GET` | `/api/users/{id}` | Szczegóły użytkownika |
| `POST` | `/api/login` | Logowanie, zwraca token |
| `GET` | `/api/users/me` | Dane zalogowanego użytkownika `🔒` |

#### POST /api/login

```json
// Request
{ "email": "sincere@april.biz", "password": "password123" }

// Response 200
{
  "token": "eb89a974...",
  "user": { "id": "...", "name": "Leanne Graham", "role": "admin" }
}
```

### Zadania

| Metoda | Endpoint | Opis |
|---|---|---|
| `POST` | `/api/tasks` | Utwórz zadanie |
| `GET` | `/api/tasks` | Lista wszystkich zadań |
| `GET` | `/api/tasks/user/{userId}` | Zadania przypisane do użytkownika |
| `PATCH` | `/api/tasks/{id}/status` | Zmień status zadania |
| `GET` | `/api/tasks/{id}/history` | Historia zdarzeń (Event Store) |

#### POST /api/tasks

```json
// Request
{
  "title": "Nowe zadanie",
  "description": "Opis zadania",
  "assignedUserId": "uuid-usera"
}

// Response 201
{ "id": "uuid-nowego-taska" }
```

#### PATCH /api/tasks/{id}/status

```json
// Request
{ "status": "in_progress" }

// Response 200
{ "message": "Status updated" }
```

Dozwolone przejścia: `todo` → `in_progress` → `done`
Błędne przejście zwraca `422 Unprocessable Entity`.

#### GET /api/tasks/{id}/history

```json
[
  {
    "eventType": "App\\Domain\\Task\\Event\\TaskCreatedEvent",
    "payload": { "title": "...", "status": "todo" },
    "occurredAt": "2026-03-14T09:00:00+01:00"
  }
]
```

---

## GraphQL API

Endpoint: `POST http://localhost:8080/graphql/graphql/default`

### Queries

```graphql
# Lista użytkowników
{ users { id name email role } }

# Pojedynczy użytkownik
{ user(id: "uuid") { name email } }

# Lista zadań z przypisanym userem
{ tasks { id title status assignedUser { name } } }

# Zadania użytkownika
{ tasksByUser(userId: "uuid") { id title status } }

# Historia zadania
{ taskHistory(id: "uuid") { eventType payload occurredAt } }
```

### Mutations

```graphql
# Utwórz zadanie
mutation {
  createTask(title: "Nowe", description: "Opis", assignedUserId: "uuid") {
    id status
  }
}

# Zmień status
mutation {
  updateTaskStatus(id: "uuid", status: "in_progress") {
    id status updatedAt
  }
}
```

---

## Panel webowy

Adres: `http://localhost:8080/web/login`

| Konto | Email | Hasło | Widok |
|---|---|---|---|
| Admin (Bret) | sincere@april.biz | password123 | Wszystkie zadania + kolumna "Przypisany do" |
| Member (Antonette) | shanna@melissa.tv | password123 | Tylko własne zadania |

---

## Testy

```bash
# Wszystkie testy
make test

# Lub bezpośrednio
docker compose exec php php bin/phpunit

# Z opisami
docker compose exec php php bin/phpunit --testdox
```

**50 testów, 59 asercji** — wszystkie przechodzą.

Pokrycie:
- `UserFactory` — tworzenie, role, pola opcjonalne, błędne dane
- `TaskFactory` — tworzenie, statusy, assignedUserId, UUID, timestampy
- `ToInProgressStrategy` — przejścia statusów, błędne przejścia
- `ToDoneStrategy` — przejścia statusów, błędne przejścia

---

## Struktura bazy danych

```
users
  id (UUID), external_id, name, username, email, role,
  password, api_token, phone, website, created_at

tasks
  id (UUID), title, description, status, assigned_user_id, created_at, updated_at

task_events
  id (UUID), aggregate_id, event_type, payload (JSON), occurred_at

messenger_messages
  (kolejka Symfony Messenger — transport doctrine)
```
