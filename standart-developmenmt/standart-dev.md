---
description: "Use when working on this IT Kode Visuak Laravel project, including controllers, requests, services, repositories, models, routes, database changes, API payloads, and related frontend assets. Enforces the MVC + Service + Repository architecture, naming rules, and project coding restrictions."
name: "IT Kode Visuak Laravel Architecture Standard"
applyTo: ["app/**/*.php", "bootstrap/**/*.php", "config/**/*.php", "database/**/*.php", "resources/**/*.php", "resources/**/*.js", "resources/**/*.css", "routes/**/*.php", "tests/**/*.php"]
---
# IT Kode Visuak Laravel Architecture Standard

Use this instruction when creating or modifying code in this workspace. Follow the project standard in [standart_dev_laravel.md](../../standart_dev_laravel.md) and apply these rules by default.

## Required Architecture

- Keep the flow: Route -> Controller -> FormRequest -> Service -> Repository -> Model -> Database.
- Use MVC + Service + Repository for feature work.
- Put business logic in `*ServiceImplement` classes.
- Put database queries in repository implementations only.
- Keep request validation in `FormRequest` classes.

## Layer Responsibilities

- Controllers only receive requests, call services, and return responses.
- Controllers must not contain direct database queries, long business logic, or direct external API calls.
- Repository interfaces and implementations should live under entity-specific folders in `app/Repositories/<Entity>/`.
- Service classes should live under entity-specific folders in `app/Services/<Entity>/` and use the `*Service.php` + `*ServiceImplement.php` pattern.
- `*ServiceImplement` may orchestrate multiple repositories, define transaction boundaries, map data, and trigger jobs, events, or notifications.
- `*ServiceImplement` must not validate request payloads directly, return `response()->json()`, or call `request()` / `Request::all()`.
- Repositories must not build HTTP responses.
- Models should not hold business logic.
- Blade views must not contain business logic.

## Repository And Service Conventions

- Create a dedicated repository interface for each entity.
- Create a matching repository implementation for each entity.
- Prefer constructor injection and explicit dependencies.
- Use `use` imports instead of fully qualified class names inline.
- When the project uses `itmm/easy-repository`, keep implementations compatible with that package's patterns.
- Bind repository interfaces to implementations in a provider such as `RepositoryServiceProvider`.

## Validation, Data, And Transactions

- Put all input validation in `FormRequest` classes.
- Pass explicit validated data or explicit parameters into services.
- Use `DB::transaction()` for changes that affect multiple tables or require an atomic business operation.
- Keep API JSON fields in `snake_case`.
- Keep array keys in `snake_case`.

## Naming Rules

- Use `PascalCase` for classes.
- Use `camelCase` for methods and variables.
- Use `snake_case` for database tables, columns, foreign keys, array keys, and JSON fields.

## Project Structure Expectations

- Place models in `app/Models/`.
- Place requests in `app/Http/Requests/`.
- Place frontend JavaScript in `resources/js/`.
- Place frontend CSS in `resources/css/`.
- Store user-uploaded files under `storage/app/public/` and use the storage symlink flow when needed.

## Prohibitions

- Do not query the database from controllers.
- Do not return responses from repositories.
- Do not hardcode credentials.
- Do not place business logic in models or Blade templates.
- Do not skip the service layer for non-trivial business flows.

## Output Expectations For Generated Code

- When adding a feature, generate the full layer split when appropriate: FormRequest, Controller changes, Service, Repository, provider binding, and tests.
- Keep code concise and readable for handoff to other developers.
- Prefer changes that reinforce this architecture instead of introducing shortcuts.

## Ambiguities To Confirm With The User

- Whether this standard should also apply to command, job, and listener classes.
- Whether small read-only controller actions may query through models directly, or must always go through repositories.
- Whether the required repository implementation suffix is `RepositoryImplement` or `Eloquent...Repository`, since the source document uses both forms.