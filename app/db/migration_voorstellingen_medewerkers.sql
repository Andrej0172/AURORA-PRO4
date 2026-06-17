-- ************************************************************
-- Migratie: Voorstellingen- en Medewerkers-tabellen aan AuroraDb toevoegen
-- Datum:      17-06-2026
-- Doel:       Nieuwe features: voorstellingen beheren en medewerkers beheren
-- ************************************************************

DROP DATABASE IF EXISTS AuroraDb;
CREATE DATABASE AuroraDb;
USE AuroraDb;

-- Step : 01
/********************************************************************************
-- Doel : Maak tabel Voorstellingen aan.
*********************************************************************************/
CREATE TABLE IF NOT EXISTS Voorstellingen
(
    Id             INT UNSIGNED    NOT NULL AUTO_INCREMENT
    ,Titel          VARCHAR(100)    NOT NULL
    ,Datum          DATE            NOT NULL
    ,Tijd           TIME            NOT NULL
    ,Zaal           VARCHAR(100)    NOT NULL
    ,AangemaaktOp   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP

    ,CONSTRAINT PK_Voorstellingen_Id PRIMARY KEY (Id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Step : 02
/********************************************************************************
-- Doel : Maak tabel Medewerkers aan.
*********************************************************************************/
CREATE TABLE IF NOT EXISTS Medewerkers
(
    Id             INT UNSIGNED    NOT NULL AUTO_INCREMENT
    ,Naam           VARCHAR(100)    NOT NULL
    ,Functie        VARCHAR(100)    NOT NULL
    ,Afdeling       VARCHAR(100)    NOT NULL
    ,AangemaaktOp   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP

    ,CONSTRAINT PK_Medewerkers_Id PRIMARY KEY (Id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Step : 03
/********************************************************************************
-- Doel : Voeg voorbeeld voorstellingen in.
*********************************************************************************/
INSERT INTO Voorstellingen (Titel, Datum, Tijd, Zaal) VALUES
('De Storm',         '2026-09-12', '20:00', 'Grote Zaal'),
('Zwanenmeer',       '2026-09-20', '19:30', 'Stadsschouwburg Amsterdam'),
('Hamlet',           '2026-10-03', '20:00', 'Kleine Zaal'),
('De Verwachting',   '2026-10-18', '20:15', 'Zuiderpershuis Antwerpen'),
('Licht & Schaduw',  '2026-11-01', '19:30', 'Grote Zaal'),
('Nachtmerrie',      '2026-11-14', '20:00', 'Theater Rotterdam'),
('Het Afscheid',     '2026-12-05', '20:00', 'Kleine Zaal');


-- Step : 04
/********************************************************************************
-- Doel : Voeg voorbeeld medewerkers in.
*********************************************************************************/
INSERT INTO Medewerkers (Naam, Functie, Afdeling) VALUES
('Sophie de Vries',   'Regisseur',       'Artistiek'),
('Lars Bakker',       'Acteur',           'Artistiek'),
('Noor van den Berg', 'Lichtontwerper',   'Techniek'),
('Daan Janssen',      'Geluidsontwerper', 'Techniek'),
('Emma Smit',         'Kostuumontwerper', 'Kostuums'),
('Tim Visser',        'Productieleider',  'Productie'),
('Lisa Meijer',       'Marketingmanager', 'Marketing');
