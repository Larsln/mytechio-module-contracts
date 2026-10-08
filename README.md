# mytechio/module-contracts

Contracts (interfaces, DTOs, fakes) between the **MyTechIO Admin core** and its
modules. A pure PHP package with no framework and no money-library dependency:
monetary amounts are strings with 4 decimal places, dates are `Y-m-d`,
timestamps are ISO-8601 — exactly like the core's domain events.

## Purpose

Modules (e.g. [`mytechio/module-domainrobot`](https://github.com/Larsln/mytechio-module-domainrobot))
need access to core functionality — document storage, mail delivery, customer
assets (Kundenobjekte), contacts, journal entries (Buchungen), invoice drafts —
without requiring the core itself as a Composer dependency. This package
defines exactly that boundary: the core binds implementations against the
interfaces, modules only inject the interfaces. In addition, the package
ships a `Fake*` for every contract for use in module tests, so modules can run
their test suite without the core.

## Installation

```bash
composer require mytechio/module-contracts
```

## Contracts

| Namespace | Interface | Core semantics |
|---|---|---|
| `Documents` | `DocumentStore` | The local GoBD-compliant storage is the owner, Paperless-NGX is an asynchronous secondary viewer; fires `DocumentStored`. |
| `Documents` | `IncomingDocuments` | Inbox ingest (Posteingang) for modules (e.g. a pull from Paperless-NGX): `ingest()` stores the document in the core storage and fires `DocumentStored`; a hash duplicate (`sha256`) returns the existing document with `duplicate = true` instead of creating a new one; `find()` returns the reference to a document by its ID, or `null`. |
| `Mail` | `MailOutbox` | Sends mail using the branding layout, records an entry in the outbox, fires `MailSent`. |
| `Assets` | `CustomerAssets` | Read/create/cancel customer assets (Kundenobjekte); `create()`/`cancel()` fire `CustomerAssetCreated`/`CustomerAssetCancelled`; `findByLabel()` returns active assets first. An asset either has a source (`source`/`externalId`, e.g. `"domainrobot"`) or a free-text provider (`providerName`, e.g. `"IONOS"`); `findBySource()` finds an asset by its external identifier, `attachSource()` retroactively assigns a source to a previously manual asset, `updateFromSource()` applies a connector update (status, renewal, autorenew, `attributes` merged), `forInvoiceItem()` returns the assets for an invoice item. |
| `Assets` | `AssetSource` | Asset suggestions from external sources for the future asset picker (target state, phase 5) — no relation to already-created customer assets is required. |
| `Contacts` | `Contacts` | Read-only contact access — modules do not create or modify contacts. |
| `Accounting` | `Journal` | Debit must equal credit (enforced via `UnbalancedEntryException`); closed periods are rejected (`PeriodClosedException`). |
| `Invoices` | `Invoices` | `createDraft()` always returns an invoice in draft status; defaults (bank account, payment terms, tax category) are filled in by the core. Finalization/sending/cancellation remain the responsibility of the core UI. `findItem()` returns the reference to an invoice item (including invoice number/status and core URL) by its ID, or `null`. `paidBetween()` returns all outgoing invoices paid within the period as `PaidDocument` (`type = "invoice"`), e.g. for the profit split. |
| `Invoices` | `InvoiceItemExtension` | A module's extension of an invoice item (see section "Invoice item extensions"). |
| `Documents` | `IncomingInvoices` | Read-only access to incoming invoices (Eingangsrechnungen). `paidBetween()` returns all incoming invoices paid within the period as `PaidDocument` (`type = "incoming_invoice"`); `find()` returns the reference to an incoming invoice by its ID, or `null`. |
| `Documents` | `InvoiceExtractor` | AI extraction provider for incoming invoices, container tag `mytechio.invoice_extractors`. The core selects exactly one driver via configuration; `extract()` returns the raw §7.6 payload (validation is done by the core) and throws `ExtractionFailedException` on transport errors or blocked generation. |
| `Articles` | `ArticleStatistics` | Read-only access to article BI analytics (revenue, cost of goods, gross profit); the core implements this contract via the core tables, a module merely displays the data. The array shapes in `ArticleOverview`/`ArticleDetailStats` match the core implementation exactly. |
| `Tax` | `VatIdChecker` | VIES check of a VAT ID. Without an active module, the core binds a null client that always throws `VatIdCheckUnavailableException` (today's "VIES unavailable" path); `InvalidVatIdFormatException` on a formally invalid VAT ID. |
| `Settings` | `ModuleSettings` | Module-specific settings; ENV/config always takes precedence over the database (`isFromEnvironment()`). Implementation not until phase 3 — only the interface and fake exist so far. |
| `Connectors` | `Connector` | A uniform set of status queries/actions for external registrars/license servers; `sync()` may run for a long time and belongs on a queue. |
| `Modules` | `ModuleLifecycle` | Lifecycle hooks (`onInstall`/`onEnable`/`onDisable`/`onUninstall`), resolved by the core via the manifest key `lifecycle`; `onEnable()` may throw (the module stays disabled), `onDisable()` must not. `AbstractModuleLifecycle` provides empty implementations to extend. |
| `Modules` | `HealthCheck` | Health status (`HealthStatus`) for the module overview; the core calls `health()` only there, and with timeout protection — implementations must not perform slow network calls. |
| `Modules` | `ModuleState` | Runtime state of a module for background code (see section "Module state in background code"): `isActive()` checks known+enabled+compatible, `isEnabled()` checks only the raw toggle. |

## Module lifecycle and health

In addition, modules can declare a `lifecycle` class in the manifest, which
the core resolves via the container:

```json
{
    "lifecycle": "MyTechIO\\Domainrobot\\DomainrobotModule",
    "roles": {
        "superadmin": ["*"],
        "admin": ["*"],
        "sales": ["domainrobot.view"]
    }
}
```

- `lifecycle` (optional): FQCN of a class that implements
  `Modules\ModuleLifecycle` and/or `Modules\HealthCheck`. The core calls the
  lifecycle hooks when enabling/disabling (order on first activation:
  migrations → `onInstall()` → `onEnable()`) and calls `health()` exclusively
  on the module overview page, with timeout protection (error ⇒
  `HealthStatus::error()`).
- `roles` (optional): default assignment of module permissions to core roles
  (`superadmin`, `admin`, `accountant`, `sales`, `viewer`); `"*"` stands for
  all of the module's permissions. If this block is absent, the previous
  behavior applies (all permissions go to `superadmin` + `admin`). Unknown
  roles result in a manifest error in the core.

## Module state in background code

A module's background code — scheduler entries, listeners on core events,
queued jobs — must only run while the module is **active**, meaning not just
enabled, but also compatible. For this, the core binds `Modules\ModuleState`,
and background code checks it first:

```php
// Scheduler entry, in addition to existing conditions
$schedule->job(new SyncDomainsJob)
    ->daily()
    ->when(fn () => app(ModuleState::class)->isActive('domainrobot'));

// Listener / job
public function handle(): void
{
    if (! app(ModuleState::class)->isActive('domainrobot')) {
        return; // exits silently, no error
    }

    // ...
}
```

- `isActive()` is the correct check for background code — it takes
  compatibility into account, not just the toggle from the modules table.
- `isEnabled()` returns only that raw toggle; background code should
  generally use `isActive()`, not `isEnabled()`.
- Synchronously invoked contract implementations (`Connector`,
  `CustomerAssets`, etc.) do NOT check `ModuleState` themselves — the core
  already filters its registries by active modules before calling an
  implementation.
- `Fakes\FakeModuleState` treats EVERY module as active by default in module
  tests — tests should not unintentionally stall just because a module name
  was not seeded. `activate()`/`deactivate()` toggle a single module for the
  duration of a test.

## Uninstallation

`php artisan module:uninstall <name>` (core) first calls the module's
`ModuleLifecycle::onUninstall()`, then removes the core's bookkeeping for the
module: the `modules` row, `module_settings`, and the module's permissions
(from all roles and from the permissions table).

**Module tables remain in place** — GoBD compliance and traceability require
that data once recorded (domains, customer assets, document references, …)
does not disappear through an uninstall. `onUninstall()` may therefore
**only** clean up technical caches/state (e.g. the health cache, cached
tokens) — it must never clear or drop domain module tables.

## Invoice item extensions

Modules can augment an invoice item with their own data without the core
needing to understand the domain content: `Invoices\InvoiceItemExtension`
stores its part under `extras[key()]` of an item. Implementations are
collected via the container tag `mytechio.invoice_item_extensions` (analogous
to `ConnectorRegistry`):

```php
app()->tag(InvoiceItemAssetsExtension::class, 'mytechio.invoice_item_extensions');
```

- `key()` **must** match the module name — the core filters out extensions
  from disabled modules using this key.
- `validate()` validates the extension's own part of `extras` (e.g. whether
  linked IDs exist and belong to the contact); errors end up in the core
  under `items.{i}.extras.{key}.{field}`.
- `afterItemsSynced()` runs after all items of an invoice have been
  recreated, inside the core transaction — this is where a module resolves
  or creates its own links, for example.
- `annotate()` returns extra lines for the document (PDF), which the core
  inserts below the item description.
- `duplicate()` returns the extension's own part of `extras` for a document
  copy (duplication/cancellation) — typically without links to the original.

Every interface is documented in the source code with English PHPDoc that
describes the behavior and core semantics in detail — that is the primary
documentation for module authors.

All DTOs are `final readonly` with constructor promotion and a `toArray()`
method (snake_case keys). `MyTechIO\Contracts\ContractException` is the
common base class for all contract exceptions.

## Extraction payload

`InvoiceExtractor::extract()` returns the raw §7.6 payload. Every extracted
leaf (head fields and per-line-item fields) carries `value` and may carry
`source_text`, `page` (1-based) and `source_box`:

```json
"source_box": {"x0": 0.62, "y0": 0.41, "x1": 0.83, "y1": 0.43}
```

`source_box` holds normalised page coordinates in `[0, 1]` with the origin at
the top left, relative to the page that `page` refers to; `x0 < x1` and
`y0 < y1`. It is optional and best-effort: drivers omit it when unsure, and
the core cross-checks it against the PDF text layer. Absent or invalid boxes
are treated as `null`.

## Fakes for module tests

Every contract has an in-memory test double under `MyTechIO\Contracts\Fakes\*`
that modules bind in their own test bench, without needing the core:

```php
use MyTechIO\Contracts\Assets\CustomerAssets;
use MyTechIO\Contracts\Fakes\FakeCustomerAssets;

// e.g. in a module's TestCase::setUp()
$this->app->instance(CustomerAssets::class, new FakeCustomerAssets);
```

- `FakeDocumentStore` — returns deterministic paths/hashes, `stored()` for assertions.
- `FakeMailOutbox` — `sent()`/`sentTo(email)` with no PHPUnit dependency.
- `FakeCustomerAssets` — in-memory with auto IDs, `seed()` for the initial state.
- `FakeContacts` — `seed()`, `setCountries()`.
- `FakeJournal` — checks debit = credit exactly using integer arithmetic (no floats),
  `seedAccount()` for the chart of accounts.
- `FakeInvoices` — remembers drafts, always returns status `draft`; `seedItem()` for `findItem()`, `seedPaidDocument()` for `paidBetween()` (sorted, filtered by period).
- `FakeModuleSettings` — `markFromEnvironment()` simulates ENV precedence.
- `FakeConnector` — configurable key/types/status/actions/events.
- `FakeModuleLifecycle` — counts calls per hook, `failOnEnable()` simulates a failing `onEnable()`.
- `FakeHealthCheck` — returns a preconfigured `HealthStatus`, `withStatus()` to reconfigure.
- `FakeInvoiceItemExtension` — records every call (`validateCalls`, `afterItemsSyncedCalls`, `annotateCalls`, `duplicateCalls`), `withValidationErrors()`/`withAnnotationLines()` to reconfigure.
- `FakeInvoiceExtractor` — returns a preconfigured payload (`returns()`) or throws a preconfigured `ExtractionFailedException` (`throws()`), counts every call (`calls()`), `withConfigured()` to toggle, `samplePayload()` provides a sample payload including `source_box`.
- `FakeArticleStatistics` — returns an empty result as long as nothing has been seeded; `seedOverview()`/`seedArticle()` set the results.
- `FakeIncomingInvoices` — `seed()` for `find()`, `seedPaidDocument()` for `paidBetween()` (sorted, filtered by period).
- `FakeVatIdChecker` — result configurable per VAT ID (`seedResult()`), `unavailable()` switches to "unavailable" (throws `VatIdCheckUnavailableException`), `calls()` for assertions.
- `FakeModuleState` — every module is active by default; `activate()`/`deactivate()` toggle a module for the test.

## Versioning

Semver: additive changes (new methods with default behavior in the fakes,
new DTO fields with a default) are minor releases; signature changes are
major releases. The core and modules compare themselves against
`MyTechIO\Contracts\Contracts::VERSION` relative to the installed package
version.

## Development

```bash
composer install
vendor/bin/pest --compact   # tests
vendor/bin/pint             # formatting
```
