# Changelog

Alle nennenswerten Änderungen an diesem Paket werden hier dokumentiert.

Das Format orientiert sich an [Keep a Changelog](https://keepachangelog.com/de/1.1.0/),
dieses Projekt folgt [Semantic Versioning](https://semver.org/lang/de/).

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
