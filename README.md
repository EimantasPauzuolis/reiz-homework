# Reiz homework

```bash
docker compose up -d --build
```

## Try it

Create a job (response is `202` with `data.id`):

```bash
curl -sS -X POST http://localhost:8080/api/jobs \
  -H 'Content-Type: application/json' \
  -d @- <<'JSON'
{
  "urls": ["https://www.scrapethissite.com/pages/"],
  "render_js": false,
  "selectors": {
    "title": "h3.page-title",
    "content": "p.lead"
  },
  "container": "div.page"
}
JSON
```

Fetch it (replace `JOB_ID` with the UUID from `data.id`):

```bash
curl -sS http://localhost:8080/api/jobs/JOB_ID
```

## Tests
```bash
docker compose exec php-fpm php artisan test
```
