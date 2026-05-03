# Nette E-shop (Maketa)

Moderní optimalizovaná maketa e-shopu postavená na frameworku **Nette (PHP 8.2+)**. 

Projekt klade důraz na čistou architekturu, prevenci N+1 problému při načítání dat z databáze a komponentový přístup k tvorbě uživatelského rozhraní (UI).

## Hlavní vlastnosti

* **Katalog se stránkováním:** Databázově optimalizované stránkování produktů.
* **Varianty a skladové kombinace:** Podpora složitých variant produktů (např. barva, velikost) a hlídání reálného stavu skladu pomocí unikátních kombinací.
* **Prevence N+1 dotazů (Data Batching):** Štítky, varianty i stavy skladu pro výpis produktů se tahají hromadně pomocí efektivních `IN (...)` klauzulí.
* **Rozdělení zodpovědností:** Logika je striktně rozdělena do menších, specializovaných služeb (`ProduktyService`, `VariantyService`, `StitkyService`, ...).
* **AJAX nákupní proces:** Vkládání do košíku a dynamické načítání modálních oken pro výběr variant probíhá bez nutnosti znovunačtení stránky (Nette Snippets).
* **Podpora více měn:** Automatický přepočet cen (CZK/EUR) pomocí `MenaService`.
* **Responzivní design:** Frontend je postaven s využitím frameworku Bootstrap.

## Použité technologie

* **Backend:** PHP 8.2, [Nette Framework](https://nette.org/)
* **Databáze:** MariaDB / MySQL (Nette Database Explorer)
* **Frontend:** HTML5, Latte šablony, CSS3, Bootstrap 5, Nette AJAX
* **Nástroje:** Tracy (pro ladění), Composer

## Architektura a struktura projektu

Aplikace využívá striktní rozvrstvení:

* **Modely (`app/Models/`):** Třídy dědící od vylepšeného `BaseModel`. Starají se čistě o komunikaci s databází a abstrahují práci s tabulkami. 
* **Služby (`app/Services/`):** Obsahují byznys logiku. Komunikují s modely a skládají z nich datové struktury připravené pro frontend.
  * `VariantyService` - komplexní řešení průniků variant a skladové dostupnosti.
  * `ObjednavkaService` - bezpečné zpracování objednávky přes databázové transakce.
  * `ProduktyService` - načítání produktů pro seznam.
  * `MenaService` - získávání aktualního kurzu CZK/EUR z webu ČNB
  * `KosikService` - práce s uživatelovým košíkem za využití session
  * `VyberVariantyService` - přistupování k session pro uživatelem vybranou kombinaci variant
  * `StitkyService` - hromadné načítání štítků.
* **Komponenty (`app/Components/`):** Tzv. "Hloupé komponenty" (Dumb Components). Dostanou předžvýkaná data ze Služeb/Presenterů a starají se pouze o jejich vykreslení a obsluhu AJAX signálů (např. `ProduktyComponent`, `KoupitModalComponent`).

## Instalace pro lokální vývoj

1. **Klonování repozitáře:**
   ```bash
   git clone <url-tveho-repozitare>
   cd <nazev-slozky>
   ```

2. **Instalace závislostí:**
   ```bash
   composer install
   ```

3. **Příprava databáze:**
   * Vytvořte si lokální databázi (např. `eshopmaketa`).
   * Importujte přiložený SQL soubor (`eshopmaketa.sql`).
   * Zkontrolujte/upravte přihlašovací údaje k databázi v souboru `config/local.neon` (pokud neexistuje, vytvořte ho podle `common.neon`).

4. **Spuštění serveru:**
   Můžete využít vestavěný PHP server v Nette:
   ```bash
   php -S localhost:8000 -t www
   ```
   Aplikace poběží na adrese `http://localhost:8000`.

## Významné optimalizace kódu

Během vývoje byl kód pečlivě refaktorován pro maximální výkon a čitelnost:
* **Paměťová optimalizace:** Modely nenačítají zbytečně tabulky do paměti (odstraněny globální cache slovníky ve službách, data se tahají přímo pro konkrétní kontext).
* **Chytré formuláře:** Dynamické generování formulářů pro výběr variant (podle IDček, nikoliv textových stringů), což drasticky snížilo zátěž na databázi a nutnost složité iterace.
