# Changelog

Alle nennenswerten Änderungen an diesem Paket werden hier dokumentiert.

Das Format orientiert sich an [Keep a Changelog](https://keepachangelog.com/de/1.1.0/),
dieses Projekt folgt [Semantic Versioning](https://semver.org/lang/de/).

## [1.4.0] - 2026-10-07

### Added

- `Documents\IncomingDocuments` (NEU) — Posteingang-Ingest für Module:
  `ingest()` legt einen Beleg im Posteingang an (GoBD-Ablage durch den
  Kern, feuert `DocumentStored`); eine Hash-Dublette (gleicher `sha256`
  des Blobs wie ein bereits lebender Beleg) legt keinen neuen Beleg an,
  sondern liefert den bestehenden mit `duplicate = true` zurück.
  `find()` liefert den Verweis auf einen Beleg anhand seiner ID, oder
  `null`.
- `Documents\IngestOptions` (NEU) — Optionen für `ingest()`: `actorId`
  und `metadata` (geht 1:1 an die Kern-Ablage durch; ein Flag wie das
  frühere `skip_notify` ist hier nicht mehr vorgesehen, Module
  entscheiden selbst anhand ihrer eigenen Daten).
- `Documents\IngestedDocument` (NEU) — Ergebnis von `ingest()`: `id`,
  `diskPath`, `sha256`, `duplicate`, `url`.
- `Documents\IncomingDocumentRef` (NEU) — Verweis auf einen
  Posteingang-Beleg: `id`, `source` (freier Modulschlüssel, z. B.
  `paperless`, oder `upload` für Kern-Uploads), `status`, `diskPath`,
  `sha256`, `incomingInvoiceId` (`null`, solange nicht einer
  Eingangsrechnung zugeordnet), `url`.
- `Fakes\FakeIncomingDocuments` (NEU) — In-Memory-Double: Dubletten-
  Erkennung per `sha256`, Auto-IDs, deterministische `diskPath`/`url`;
  `seed()` hinterlegt einen Beleg direkt für `find()`, `ingested()`
  liefert alle Aufrufe von `ingest()` für Assertions.
- README: Zeile für `Documents\IncomingDocuments` in der Verträge-Tabelle.
- `Contracts::VERSION` auf `1.4.0`.

## [1.3.0] - 2026-10-06

### Added

- `Contacts\ContactData`: neue optionale Felder `mobile`, `website`, `updatedAt` (angehängt, Defaults null).
- `Invoices\InvoiceItemRef` (NEU) — Verweis auf eine Rechnungsposition
  inkl. ihrer Rechnung (`id`, `invoiceId`, `invoiceNumber`,
  `invoiceStatus`, `description`, `url`); `invoiceNumber` ist `null` für
  Draft-Rechnungen, `url` ist die relative Kern-URL der Rechnung.
- `Invoices\Invoices::findItem()` — liefert den `InvoiceItemRef` zu einer
  Rechnungsposition anhand ihrer ID, oder `null`.
- `Fakes\FakeInvoices::seedItem()` — hinterlegt einen `InvoiceItemRef` für
  `findItem()` im In-Memory-Double.
- `Contracts::VERSION` auf `1.3.0`.

## [1.2.0] - 2026-10-06

### Added

- `Assets\CustomerAssetData` / `Assets\NewCustomerAsset` — neue Felder
  `source`, `externalId`, `providerName`, `renewsAt`, `autorenew`,
  `externalStatus` (nur `CustomerAssetData`) und `attributes` (alle mit
  Default), damit Kundenobjekte eine externe Quelle (z. B.
  `"domainrobot"`) oder einen Freitext-Anbieter (z. B. `"IONOS"`) sowie
  typspezifische Attribute führen können.
- `Assets\CustomerAssets::findBySource()` — findet ein Kundenobjekt
  anhand seiner externen Kennung.
- `Assets\CustomerAssets::attachSource()` — ordnet ein bisher manuelles
  Objekt (Altbestand) nachträglich einer Quelle zu.
- `Assets\CustomerAssets::updateFromSource()` — übernimmt eine
  Connector-Rückmeldung (`Connectors\ConnectorStatus`) in das
  Kundenobjekt; `attributes` werden gemergt, nicht ersetzt.
- `Assets\CustomerAssets::forInvoiceItem()` — liefert die Kundenobjekte
  einer Rechnungsposition.
- `Invoices\InvoiceItemExtension` (NEU) — Positions-Erweiterung einer
  Rechnung durch ein Modul: eigener Teil der `extras` einer Position
  unter dem Schlüssel `key()` (muss dem Modulnamen entsprechen),
  `validate()`, `afterItemsSynced()`, `annotate()` (Beleg-Zusatzzeilen)
  und `duplicate()` (Belegkopie). Implementierungen werden über den
  Container-Tag `mytechio.invoice_item_extensions` gesammelt.
- `Fakes\FakeCustomerAssets` um `findBySource()`, `attachSource()`,
  `updateFromSource()` und `forInvoiceItem()` erweitert (In-Memory).
- `Fakes\FakeInvoiceItemExtension` (NEU) — zeichnet jeden Aufruf auf
  (`validateCalls`, `afterItemsSyncedCalls`, `annotateCalls`,
  `duplicateCalls`), `withValidationErrors()`/`withAnnotationLines()`
  zum Umkonfigurieren der Rückgabewerte.
- README: Abschnitt „Positions-Erweiterung" (Tag, `key()` = Modulname,
  Zusammenspiel `validate`/`afterItemsSynced`/`annotate`/`duplicate`),
  `CustomerAssets`-Zeile um Quelle/Anbieter/Attribute ergänzt.
- `Contracts::VERSION` auf `1.2.0`.

## [1.1.0] - 2026-10-06

### Added

- `Modules\ModuleLifecycle` (+ `Modules\AbstractModuleLifecycle`) —
  Lebenszyklus-Hooks `onInstall()`/`onEnable()`/`onDisable()`/
  `onUninstall()`, vom Kern über den Manifest-Schlüssel `lifecycle`
  aufgelöst; `onEnable()` darf werfen (Modul bleibt deaktiviert),
  `onDisable()` nicht.
- `Modules\HealthCheck` (+ `Modules\HealthStatus`) — Gesundheitsstatus für
  die Modulübersicht (`ok`/`warning`/`error`/`unknown` über statische
  Konstruktoren); der Kern ruft `health()` nur dort und mit
  Timeout-Schutz auf.
- Manifest-Erweiterungen dokumentiert: `lifecycle` (FQCN der
  Lifecycle-/HealthCheck-Klasse) und `roles` (Standardzuordnung der
  Modul-Permissions zu Kern-Rollen, `"*"` = alle Permissions).
- `Fakes\FakeModuleLifecycle` (Aufrufzähler je Hook, `failOnEnable()`) und
  `Fakes\FakeHealthCheck` (vorgegebener Status, `withStatus()`) samt
  Unit-Tests.
- `Contracts::VERSION` auf `1.1.0`.

## [1.0.0] - 2026-10-06

### Added

- Erstveröffentlichung des Vertragspakets zwischen dem MyTechIO-Admin-Kern und seinen
  Modulen: Interfaces, DTOs und Fakes ohne jede Framework- oder Money-Abhängigkeit
  (Geldbeträge als Strings mit 4 Nachkommastellen, Daten als `Y-m-d`, Zeitstempel
  ISO-8601).
- `Documents\DocumentStore` (+ `DocumentMeta`, `StoredDocument`) — Dokumentablage.
- `Mail\MailOutbox` (+ `OutgoingMail`, `MailRecipient`, `MailAttachment`, `SentMail`)
  — Mailversand mit Branding-Layout und Postausgang.
- `Assets\CustomerAssets` (+ `CustomerAssetData`, `NewCustomerAsset`, `AssetType`,
  `AssetNotFoundException`) — Kundenobjekte lesen/anlegen/kündigen.
- `Assets\AssetSource` (+ `AssetSuggestion`) — Objekt-Vorschläge für den künftigen
  Objekt-Picker (Zielbild Phase 5).
- `Contacts\Contacts` (+ `ContactData`) — rein lesender Kontaktzugriff.
- `Accounting\Journal` (+ `JournalEntryDraft`, `JournalLineDraft`, `Side`,
  `JournalEntryRef`, `ActorRef`, `AccountInfo`, `UnbalancedEntryException`,
  `PeriodClosedException`) — Buchungssätze mit erzwungenem Soll=Haben.
- `Invoices\Invoices` (+ `InvoiceDraft`, `InvoiceDraftItem`, `InvoiceRef`) —
  Rechnungs-Entwürfe.
- `Settings\ModuleSettings` — modulspezifische Einstellungen (Implementierung erst
  Phase 3, hier nur Interface + Fake).
- `Connectors\Connector` (+ `SyncReport`, `ConnectorStatus`, `ConnectorAction`) —
  Objekt-Connector für Registrare/Lizenzserver.
- `ContractException` als gemeinsame Basisklasse aller Vertrags-Ausnahmen.
- Fakes unter `Fakes\*` für jeden Vertrag, jeweils mit Unit-Tests;
  `FakeJournal` erzwingt die Soll=Haben-Invariante exakt über
  Integer-Arithmetik auf Minor-Units (keine Floats).
- `Contracts::VERSION` als Vergleichsbasis für die Semver-Kompatibilitätsprüfung
  des Kerns.
