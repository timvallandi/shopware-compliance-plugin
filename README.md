# TvWebDev Product Compliance

## Installation

```bash
bin/console plugin:refresh
bin/console plugin:install --activate TvWebDevProductCompliance
bin/console database:migrate TvWebDevProductCompliance --all
bin/console cache:clear
bin/build-administration.sh
bin/build-storefront.sh
```

Falls die Umgebung die Assets automatisch baut, reichen `plugin:refresh`, `plugin:install --activate` und `cache:clear`.

## Verwendung

Im Produktdetail der Administration erscheint im Spezifikationen-/Zusatzfelder-Tab die Karte "Produkt- & Compliance-Hinweis". Dort:

1. "Besonderer Hinweis erforderlich" aktivieren.
2. "Hinweistext" pflegen.
3. Produkt speichern.

In der Storefront erscheint die Hinweisbox im Buy-Widget der Produktdetailseite vor dem Warenkorb-Formular.

Das TvWebDev-Custom-Field-Set wird in diesem Produkt-Tab aus der Standard-Zusatzfelder-Karte herausgefiltert. Dadurch gibt es im Admin nur den kontrollierten Pflegeweg, bei dem die Textarea deaktiviert bleibt, solange die Checkbox nicht aktiv ist.

Die Felder sind auch ueber die Admin API pflegbar:

```http
PATCH /api/product/{productId}
Content-Type: application/json

{
  "customFields": {
    "tvwebdev_product_compliance_required": true,
    "tvwebdev_product_compliance_notice": "Dieses Produkt darf nur nach fachlicher Beratung angewendet werden."
  }
}
```

## Technische Entscheidung

Die Speicherung erfolgt via Product Custom Fields:

- `tvwebdev_product_compliance_required` als Boolean.
- `tvwebdev_product_compliance_notice` als Text.

Das ist hier bewusst schlanker als eine eigene Entity Extension, weil die Aufgabe genau zwei produktbezogene Attribute ohne eigene Relationen, Historie, Freigabeprozess oder komplexe Abfragen beschreibt. Custom Fields sind in Shopware 6.6 nativ administrierbar, migrationsfähig, API-fähig und passen zu einfachen Produkt-Metadaten.

Die Storefront-Logik liegt in `ProductComplianceNoticeResolver` und `ProductComplianceSubscriber`. Der Subscriber hängt nur dann die Struct-Extension `tvwebdevProductComplianceNotice` an das Produkt, wenn beide fachlichen Bedingungen erfüllt sind. Twig prüft dadurch nicht die Custom-Field-Regeln, sondern rendert nur eine vorbereitete View-Erweiterung.

## Beantwortung der Konzeptfragen

### 1. Wie würdest du die Logik erweitern, wenn der Hinweis nur für bestimmte Kundengruppen (z.B. B2B) sichtbar sein soll?

Für reine Sichtbarkeitsregeln könnte ein weiteres Custom Field wie `tvwebdev_product_compliance_customer_group_ids` erstellt werden. Der Resolver würde die aktuelle Customer Group aus dem `SalesChannelContext` auswerten und die Extension nur bei passender Gruppe setzen.

Sobald Regeln komplexer werden, wäre eine eigene Entity sinnvoll, z. B. `tvwebdev_product_compliance_notice` mit Relationen zu Produkt, Customer Group, Sales Channel, Sprache und Gültigkeitszeitraum. Alternativ könnte man Shopware Rule Builder Regeln referenzieren, wenn Fachanwender komplexe Bedingungen pflegen sollen.

### 2. Wie würdest du vorgehen, wenn rechtliche Hinweise versioniert und historisch nachvollziehbar sein müssen?

Für revisionssichere Hinweise reichen Custom Fields nicht aus. Dann braucht es ein eigenes versioniertes Datenmodell mit Feldern wie Produkt-ID, Version, Hinweistext, gültig ab/bis, Status, Autor, Freigabezeitpunkt und optional Hash/Signatur. Beim Kauf sollte die konkrete Hinweisversion an Order Line Items oder Order Custom Fields persistiert werden, damit später nachvollziehbar ist, welcher Text zum Kaufzeitpunkt galt.

### 3. Warum sollte Geschäftslogik nicht im Twig-Template implementiert werden?

Twig soll die Darstellung übernehmen, nicht Fachentscheidungen. Geschäftslogik in Templates ist schwerer testbar, wird schnell dupliziert und vermischt Cache-/Rendering-Fragen mit fachlichen Regeln. Ein Service/Subscriber ist wiederverwendbar, testbar und kann später Store API, CMS, Checkout oder Kundengruppenlogik bedienen, ohne Template-Regeln zu kopieren.

### 4. Welche Kriterien würdest du nutzen, um zwischen Custom Field und Entity Extension zu entscheiden (Shopware 6.6)?

Custom Fields eignen sich für einfache, produktbezogene Metadaten mit wenigen Feldern, ohne eigene Relationen, ohne komplexe Suche und ohne Lebenszyklus. Sie sind schnell administrierbar und über die Admin API direkt pflegbar.

Eine Entity Extension oder eigene Entity ist besser, wenn strukturierte Daten, Relationen, Versionierung, Übersetzungen mit eigenem Workflow, Gültigkeiten, Freigaben, eigene ACL, eigene Listen oder performante Filter/Joins benötigt werden.

## Annahmen und Vereinfachungen

- Die Hinweisbox wird nur auf der Produktdetailseite angezeigt.
- Die Admin-Karte schreibt in dieselben Custom Fields wie die Standard-Custom-Field-Verwaltung und die Admin API.
- Wenn die Checkbox deaktiviert wird, leert die Admin-Karte den Hinweistext, damit kein veralteter Text versehentlich gepflegt bleibt.
- Für echte Rechtsrevision, B2B-Regelwerke oder Freigabeprozesse wäre ein eigenes Datenmodell der nächste Ausbauschritt.
