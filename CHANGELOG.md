# Changelog

All notable changes to this package are documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/).

## [1.6.1] - 2026-10-07

### Changed

- Documentation: all PHPDoc and README in English; no code changes.

## [1.6.0] - 2026-10-07

### Added

- `Modules\ModuleState` (NEW) — runtime state of a module for background
  code: `isActive()` checks known+enabled+compatible (= the check for
  scheduler entries/listeners/jobs), `isEnabled()` returns only the raw
  toggle from the modules table.
- `Fakes\FakeModuleState` (NEW) — every module is active by default
  (tests should not unintentionally stall); `activate()`/
  `deactivate()` toggle a single module for the duration of a test.
- README: new sections "Module state in background code" (scheduler/
  listener/job rule using `ModuleState::isActive()`) and "Uninstallation"
  (`module:uninstall` calls `onUninstall()`, removes core bookkeeping;
  module tables remain in place due to GoBD, `onUninstall()` only cleans
  up technical caches/state); row for `ModuleState` in the contracts
  table, `FakeModuleState` in the fakes section.
- `Contracts::VERSION` bumped to `1.6.0`.

## [1.5.0] - 2026-10-07

### Added

- `Documents\InvoiceExtractor` (NEW) — AI extraction provider for
  incoming invoices, container tag `mytechio.invoice_extractors`:
  `extractorKey()`, `label()`, `configured()`, `extract()` returns the
  raw §7.6 payload (validation is done by the core) and throws
  `ExtractionFailedException` on transport errors or blocked generation.
- `Documents\ExtractionContextData` (NEW) — context for `extract()`:
  active expense accounts (`expenseAccounts`) and the canonical list of
  units (`units`).
- `Documents\ExtractionFailedException` (NEW) — thrown by
  `InvoiceExtractor::extract()`.
- `Fakes\FakeInvoiceExtractor` (NEW) — configurable payload
  (`returns()`) or exception (`throws()`), counts calls (`calls()`).
- `Articles\ArticleStatistics` (NEW) — read-only access to the
  article BI analytics: `overview()` returns an `ArticleOverview`,
  `forArticle()` returns an `ArticleDetailStats` or `null`.
- `Articles\ArticleOverview` / `Articles\ArticleDetailStats` (NEW) — array
  shapes match the core implementation exactly
  (`ArticleAnalyticsService::overview()`/`articleDetail()`);
  `ArticleDetailStats::$priceHistory` bundles the core's separate purchase/
  sale price series under the keys `purchase`/`sale`.
- `Fakes\FakeArticleStatistics` (NEW) — returns an empty result as long
  as nothing has been seeded; `seedOverview()`/`seedArticle()`.
- `Invoices\Invoices::paidBetween()` (NEW) — returns all outgoing invoices
  paid within the period as `list<PaidDocument>`
  (`type = "invoice"`), e.g. for the profit split.
- `Invoices\PaidDocument` (NEW) — read-only reference to a paid
  document (outgoing or incoming invoice): `type`, `id`, `number`,
  `paidOn`, `netAmount`, `contactId`, `url`.
- `Documents\IncomingInvoices` (NEW) — read-only access to
  incoming invoices: `paidBetween()` (`type = "incoming_invoice"`),
  `find()` returns an `IncomingInvoiceRef` or `null`.
- `Documents\IncomingInvoiceRef` (NEW) — read-only reference to an
  incoming invoice: `id`, `number`, `status`, `contactId`, `url`.
- `Fakes\FakeInvoices` extended with `paidBetween()`/`seedPaidDocument()`.
- `Fakes\FakeIncomingInvoices` (NEW) — `seed()` for `find()`,
  `seedPaidDocument()` for `paidBetween()`.
- `Tax\VatIdChecker` (NEW) — VIES check of a VAT ID: `check()` returns
  a `VatIdCheckResult` or throws `VatIdCheckUnavailableException`
  (service unavailable) or `InvalidVatIdFormatException` (format).
- `Tax\VatIdCheckResult` (NEW) — `valid`, `name`, `address`,
  `requestIdentifier`, `checkedAt`.
- `Tax\VatIdCheckUnavailableException` / `Tax\InvalidVatIdFormatException`
  (NEW) — both `extends ContractException`.
- `Fakes\FakeVatIdChecker` (NEW) — result configurable per VAT ID
  (`seedResult()`), `unavailable()` simulates "unavailable".
- README: rows for `IncomingInvoices`, `InvoiceExtractor`,
  `ArticleStatistics`, `VatIdChecker` in the contracts table; new fakes
  in the fakes section.
- `Contracts::VERSION` bumped to `1.5.0`.

## [1.4.0] - 2026-10-07

### Added

- `Documents\IncomingDocuments` (NEW) — inbox ingest for modules:
  `ingest()` stores a document in the inbox (GoBD-compliant storage by
  the core, fires `DocumentStored`); a hash duplicate (same `sha256`
  of the blob as an already existing document) does not create a new
  document, but returns the existing one with `duplicate = true`.
  `find()` returns the reference to a document by its ID, or
  `null`.
- `Documents\IngestOptions` (NEW) — options for `ingest()`: `actorId`
  and `metadata` (passed through 1:1 to the core storage; a flag like
  the former `skip_notify` is no longer provided here, modules
  decide for themselves based on their own data).
- `Documents\IngestedDocument` (NEW) — result of `ingest()`: `id`,
  `diskPath`, `sha256`, `duplicate`, `url`.
- `Documents\IncomingDocumentRef` (NEW) — reference to an
  inbox document: `id`, `source` (free module key, e.g.
  `paperless`, or `upload` for core uploads), `status`, `diskPath`,
  `sha256`, `incomingInvoiceId` (`null` as long as not linked to
  an incoming invoice), `url`.
- `Fakes\FakeIncomingDocuments` (NEW) — in-memory double: duplicate
  detection via `sha256`, auto IDs, deterministic `diskPath`/`url`;
  `seed()` stores a document directly for `find()`, `ingested()`
  returns all calls to `ingest()` for assertions.
- README: row for `Documents\IncomingDocuments` in the contracts table.
- `Contracts::VERSION` bumped to `1.4.0`.

## [1.3.0] - 2026-10-06

### Added

- `Contacts\ContactData`: new optional fields `mobile`, `website`, `updatedAt` (appended, default null).
- `Invoices\InvoiceItemRef` (NEW) — reference to an invoice item
  including its invoice (`id`, `invoiceId`, `invoiceNumber`,
  `invoiceStatus`, `description`, `url`); `invoiceNumber` is `null` for
  draft invoices, `url` is the relative core URL of the invoice.
- `Invoices\Invoices::findItem()` — returns the `InvoiceItemRef` for an
  invoice item by its ID, or `null`.
- `Fakes\FakeInvoices::seedItem()` — stores an `InvoiceItemRef` for
  `findItem()` in the in-memory double.
- `Contracts::VERSION` bumped to `1.3.0`.

## [1.2.0] - 2026-10-06

### Added

- `Assets\CustomerAssetData` / `Assets\NewCustomerAsset` — new fields
  `source`, `externalId`, `providerName`, `renewsAt`, `autorenew`,
  `externalStatus` (only on `CustomerAssetData`) and `attributes` (all with
  a default), so customer assets can carry an external source (e.g.
  `"domainrobot"`) or a free-text provider (e.g. `"IONOS"`) as well as
  type-specific attributes.
- `Assets\CustomerAssets::findBySource()` — finds a customer asset
  by its external identifier.
- `Assets\CustomerAssets::attachSource()` — retroactively assigns a
  previously manual asset (legacy data) to a source.
- `Assets\CustomerAssets::updateFromSource()` — applies a
  connector update (`Connectors\ConnectorStatus`) to the
  customer asset; `attributes` are merged, not replaced.
- `Assets\CustomerAssets::forInvoiceItem()` — returns the customer assets
  for an invoice item.
- `Invoices\InvoiceItemExtension` (NEW) — a module's extension of an
  invoice item: its own part of an item's `extras` under the key
  `key()` (must match the module name),
  `validate()`, `afterItemsSynced()`, `annotate()` (extra document
  lines), and `duplicate()` (document copy). Implementations are
  collected via the container tag `mytechio.invoice_item_extensions`.
- `Fakes\FakeCustomerAssets` extended with `findBySource()`, `attachSource()`,
  `updateFromSource()` and `forInvoiceItem()` (in-memory).
- `Fakes\FakeInvoiceItemExtension` (NEW) — records every call
  (`validateCalls`, `afterItemsSyncedCalls`, `annotateCalls`,
  `duplicateCalls`), `withValidationErrors()`/`withAnnotationLines()`
  to reconfigure the return values.
- README: section "Invoice item extensions" (tag, `key()` = module name,
  interaction of `validate`/`afterItemsSynced`/`annotate`/`duplicate`),
  `CustomerAssets` row extended with source/provider/attributes.
- `Contracts::VERSION` bumped to `1.2.0`.

## [1.1.0] - 2026-10-06

### Added

- `Modules\ModuleLifecycle` (+ `Modules\AbstractModuleLifecycle`) —
  lifecycle hooks `onInstall()`/`onEnable()`/`onDisable()`/
  `onUninstall()`, resolved by the core via the manifest key
  `lifecycle`; `onEnable()` may throw (the module stays disabled),
  `onDisable()` must not.
- `Modules\HealthCheck` (+ `Modules\HealthStatus`) — health status for
  the module overview (`ok`/`warning`/`error`/`unknown` via static
  constructors); the core calls `health()` only there and with
  timeout protection.
- Manifest extensions documented: `lifecycle` (FQCN of the
  lifecycle/health-check class) and `roles` (default assignment of
  module permissions to core roles, `"*"` = all permissions).
- `Fakes\FakeModuleLifecycle` (call counter per hook, `failOnEnable()`) and
  `Fakes\FakeHealthCheck` (preconfigured status, `withStatus()`) along with
  unit tests.
- `Contracts::VERSION` bumped to `1.1.0`.

## [1.0.0] - 2026-10-06

### Added

- Initial release of the contracts package between the MyTechIO Admin core
  and its modules: interfaces, DTOs and fakes with no framework or
  money-library dependency (monetary amounts as strings with 4 decimal
  places, dates as `Y-m-d`, timestamps as ISO-8601).
- `Documents\DocumentStore` (+ `DocumentMeta`, `StoredDocument`) — document storage.
- `Mail\MailOutbox` (+ `OutgoingMail`, `MailRecipient`, `MailAttachment`, `SentMail`)
  — mail delivery with branding layout and outbox.
- `Assets\CustomerAssets` (+ `CustomerAssetData`, `NewCustomerAsset`, `AssetType`,
  `AssetNotFoundException`) — read/create/cancel customer assets.
- `Assets\AssetSource` (+ `AssetSuggestion`) — asset suggestions for the future
  asset picker (target state, phase 5).
- `Contacts\Contacts` (+ `ContactData`) — read-only contact access.
- `Accounting\Journal` (+ `JournalEntryDraft`, `JournalLineDraft`, `Side`,
  `JournalEntryRef`, `ActorRef`, `AccountInfo`, `UnbalancedEntryException`,
  `PeriodClosedException`) — journal entries with enforced debit = credit.
- `Invoices\Invoices` (+ `InvoiceDraft`, `InvoiceDraftItem`, `InvoiceRef`) —
  invoice drafts.
- `Settings\ModuleSettings` — module-specific settings (implementation not
  until phase 3, only interface + fake so far).
- `Connectors\Connector` (+ `SyncReport`, `ConnectorStatus`, `ConnectorAction`) —
  asset connector for registrars/license servers.
- `ContractException` as the common base class for all contract exceptions.
- Fakes under `Fakes\*` for every contract, each with unit tests;
  `FakeJournal` enforces the debit = credit invariant exactly using
  integer arithmetic on minor units (no floats).
- `Contracts::VERSION` as the comparison basis for the core's Semver
  compatibility check.
