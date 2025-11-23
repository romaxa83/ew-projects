# H2H CRM Application

---

## Local Development (Frontend)

> Note! Docker required

### First time setup

1. Define environment variables in local `.env` file (git ignored)
2. Start dev container by running `npm run dev-up`
3. Inside the container, from `/app` directory:
   - Run `npm run build-smartadmin-ui`
   - Run `npm install`
   - Run `npm run postinstall`

### Incremental development

1. Start dev container by running `npm run dev-up` or `npm run dev-exec`
2. Inside the container, from `/app` directory:
   - Run `npm run watch`
   - Browse to `http://localhost`

### Stopping the dev container

1. If you inside the container, run `exit`
2. Run `npm run dev-down`

### Troubleshooting

If you encounter a problem `Fatal error: Uncaught Error: Failed opening required '/app/public/../vendor/autoload.php'` or similar
1. Open a new terminal window and run `docker exec -it H2HMovers-Dev__php sh`
2. Inside the container run `composer dump-autoload`

---
