# YoPrint Laravel Coding Test

This project demonstrates a CSV upload and processing system in Laravel, including chunked job processing with progress tracking.

## Requirements

* Docker & Docker Compose installed
* No other applications using the following ports:
  `80`, `9000`, `8080`, `3306`, `6379`

---

## Setup & Run

1. **Start Docker containers**

```bash
docker compose up --build -d
```

2. **Enter the application container**

```bash
docker compose exec app bash
```

3. **Run database migrations**

```bash
php artisan migrate
```

4. **Access the application**

Open your browser at:
[http://localhost](http://localhost)
