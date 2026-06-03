-- ************************************************************
-- Volledig opstartscript voor de accounts-database (schema + testdata)
-- Versie    Datum       Auteur            Omschrijving
-- ******    ******      ******            ************
-- 01        05-03-2026  Tristan           Database aangemaakt
-- 02        06-03-2026  Tristan           INSERT statements lidmaatschappen verbeterd
-- 03        07-03-2026  Joey              Tabel Reserveringen toegevoegd
-- 04        09-03-2026  Silvan            Formatting
-- 05        21-03-2026  Joey              Status Verlopen toegevoegd aan Reserveringen ENUM
-- 06        21-03-2026  Silvan            ProfielFoto gewijzigd naar MEDIUMBLOB, ProfielFotoMime toegevoegd
-- ************************************************************

-- Database verwijderen en opnieuw maken
DROP DATABASE IF EXISTS AuroraAccountsDb;
CREATE DATABASE AuroraAccountsDb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE AuroraAccountsDb;


-- Step : 01.1
/********************************************************************************
-- Doel : Maak tabel Lidmaatschappen aan.
-- ******************************************************************************
-- Versie     Datum          Auteur          Omschrijving
-- ******     **********     **********      **************
-- 01         05-03-2026     Tristan         Tabel Lidmaatschappen aangemaakt
-- 02         09-03-2026     Silvan          Formatting
*********************************************************************************/

-- Tabel voor verschillende lidmaatschappen (basis, standaard, premium, etc.)
CREATE TABLE Lidmaatschappen
(
    Id             INT             NOT NULL AUTO_INCREMENT
    ,Naam           VARCHAR(50)     NOT NULL
    ,Beschrijving   TEXT            NOT NULL
    ,PrijsPerMaand  DECIMAL(6,2)    NOT NULL
    ,Toegang        VARCHAR(255)    NOT NULL
    ,Actief         TINYINT         NOT NULL DEFAULT 1

    ,CONSTRAINT PK_Lidmaatschappen_Id PRIMARY KEY (Id)
) ENGINE=InnoDB;


-- Step : 02.1
/********************************************************************************
-- Doel : Maak tabel Accounts aan.
-- ******************************************************************************
-- Versie     Datum          Auteur          Omschrijving
-- ******     **********     **********      **************
-- 01         05-03-2026     Tristan         Tabel Accounts aangemaakt
-- 02         09-03-2026     Silvan          Formatting
*********************************************************************************/

-- Gebruikersaccounts voor leden en medewerkers
CREATE TABLE Accounts
(
    Id             INT             NOT NULL AUTO_INCREMENT
    ,Voornaam       VARCHAR(50)     NOT NULL
    ,Tussenvoegsel  VARCHAR(20)     NULL DEFAULT NULL
    ,Achternaam     VARCHAR(50)     NOT NULL
    ,Email          VARCHAR(100)    NOT NULL
    ,Telefoon       VARCHAR(20)     NULL DEFAULT NULL
    ,Geboortedatum  DATE            NOT NULL
    ,LidmaatschapId INT             NOT NULL
    ,StartDatum     DATE            NOT NULL
    ,EindDatum      DATE            NULL DEFAULT NULL
    ,Status         ENUM('Actief', 'Verlopen', 'Gepauzeerd', 'Opgezegd') NOT NULL DEFAULT 'Actief'
    ,Rol            ENUM('lid', 'medewerker')                            NOT NULL DEFAULT 'lid'
    ,Wachtwoord     VARCHAR(255)    NOT NULL
    ,ProfielFoto    MEDIUMBLOB      NULL DEFAULT NULL
    ,ProfielFotoMime VARCHAR(50)    NULL DEFAULT NULL
    ,AangemaaktOp   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP

    ,CONSTRAINT PK_Accounts_Id             PRIMARY KEY (Id)
    ,CONSTRAINT UQ_Accounts_Email          UNIQUE      (Email)
    ,CONSTRAINT UQ_Accounts_Telefoon       UNIQUE      (Telefoon)
    ,CONSTRAINT FK_Accounts_LidmaatschapId FOREIGN KEY (LidmaatschapId) REFERENCES Lidmaatschappen(Id)
) ENGINE=InnoDB;


-- Step : 03.1
/********************************************************************************
-- Doel : Maak tabel Reserveringen aan.
-- ******************************************************************************
-- Versie     Datum          Auteur          Omschrijving
-- ******     **********     **********      **************
-- 01         07-03-2026     Joey            Nieuw
-- 02         09-03-2026     Silvan          Formatting
-- 03         21-03-2026     Joey            Status 'Verlopen' toegevoegd aan ENUM
*********************************************************************************/

-- Gemaakte reserveringen voor lessen
CREATE TABLE Reserveringen
(
    Id             INT UNSIGNED    NOT NULL AUTO_INCREMENT
    ,AccountId      INT             NOT NULL
    ,LesId          INT UNSIGNED    NOT NULL
    ,Datum          DATE            NOT NULL
    ,Status         ENUM('Bevestigd', 'Geannuleerd', 'Wachtlijst', 'Verlopen') NOT NULL DEFAULT 'Bevestigd'
    ,AangemaaktOp   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP

    ,CONSTRAINT PK_Reserveringen_Id PRIMARY KEY (Id)
) ENGINE=InnoDB;


-- Step : 03.2
/********************************************************************************
-- Doel : Maak tabel RememberTokens aan.
-- ******************************************************************************
-- Versie     Datum          Auteur          Omschrijving
-- ******     **********     **********      **************
-- 01         10-03-2026     Silvan          Tabel RememberTokens aangemaakt
*********************************************************************************/

-- Tokens voor "Onthoud mij" functionaliteit
CREATE TABLE RememberTokens
(
    Id             INT UNSIGNED    NOT NULL AUTO_INCREMENT
    ,AccountId      INT             NOT NULL
    ,Token          VARCHAR(64)     NOT NULL
    ,VerlooptOp     DATETIME        NOT NULL
    ,AangemaaktOp   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP

    ,CONSTRAINT PK_RememberTokens_Id PRIMARY KEY (Id)
    ,CONSTRAINT FK_RememberTokens_AccountId FOREIGN KEY (AccountId) REFERENCES Accounts(Id) ON DELETE CASCADE
) ENGINE=InnoDB;


-- Step : 04.1
/********************************************************************************
-- Doel : Voeg rijen toe aan tabel Lidmaatschappen.
-- ******************************************************************************
-- Versie     Datum          Auteur          Omschrijving
-- ******     **********     **********      **************
-- 01         05-03-2026     Tristan         Rijen toegevoegd aan Lidmaatschappen
-- 02         06-03-2026     Tristan         INSERT statements verbeterd
-- 03         09-03-2026     Silvan          Formatting
*********************************************************************************/

-- Basis lidmaatschappen toevoegen
INSERT INTO Lidmaatschappen (Naam, Beschrijving, PrijsPerMaand, Toegang)
VALUES
('Basis',     'Toegang tot de fitnesszaal tijdens daluren (09:00 - 16:00). Ideaal voor beginners.',             19.99, 'Fitnesszaal (daluren)')
     ,('Standaard', 'Onbeperkt toegang tot de fitnesszaal en groepslessen. De populairste keuze.',                    34.99, 'Fitnesszaal, Groepslessen')
     ,('Premium',   'Volledige toegang tot alle faciliteiten inclusief zwembad, sauna en persoonlijk trainingsplan.', 54.99, 'Fitnesszaal, Groepslessen, Zwembad, Sauna')
     ,('Student',   'Speciaal tarief voor studenten met geldig studentenpas. Onbeperkt toegang tot fitnesszaal.',     14.99, 'Fitnesszaal, Groepslessen')
     ,('Gezin',     'Lidmaatschap voor het hele gezin (max 4 personen). Toegang tot alle faciliteiten.',              89.99, 'Fitnesszaal, Groepslessen, Zwembad, Sauna')
     ,('Dagpas',    'Eenmalige toegang voor een dag. Geen abonnement nodig.',                                          9.99, 'Fitnesszaal, Groepslessen');


-- Step : 04.2
/********************************************************************************
-- Doel : Voeg rijen toe aan tabel Accounts.
-- ******************************************************************************
-- Versie     Datum          Auteur          Omschrijving
-- ******     **********     **********      **************
-- 01         05-03-2026     Tristan         Rijen toegevoegd aan Accounts
-- 02         09-03-2026     Silvan          Formatting
-- 03         10-03-2026     Silvan          Teruggebracht naar 1 lid en 1 admin
*********************************************************************************/

-- Standaard lid
INSERT INTO Accounts (Voornaam, Tussenvoegsel, Achternaam, Email, Telefoon, Geboortedatum, LidmaatschapId, StartDatum, EindDatum, Status, Wachtwoord, AangemaaktOp)
VALUES
    ('Jan', 'de', 'Vries', 'jan.devries@email.nl', '0612345678', '1990-03-15', 2, '2025-01-10', '2028-02-22', 'Actief', '$2y$10$SS5Iye51H.xSJSS6NrR6aucNfXoor4P7HoMgUUKChgMnxFAFKH4E.', '2025-01-05 10:30:00');

-- Admin account
INSERT INTO Accounts (Id, Voornaam, Tussenvoegsel, Achternaam, Email, Telefoon, Geboortedatum, LidmaatschapId, StartDatum, EindDatum, Status, Rol, Wachtwoord, AangemaaktOp)
VALUES
    (2, 'Jacob', NULL, 'Jenever', 'j.jenever@admin.nl', '0686172008', '1977-07-14', 1, '2024-01-01', NULL, 'Actief', 'medewerker', '$2y$10$hma7LpwrREvBT8f12788Q.74Pss24N9hg7918p7gc4leLA.HqsWmm', '2024-01-01 00:00:00');

-- 15 nieuwe leden voor Yoga voor beginners
INSERT INTO Accounts (Voornaam, Tussenvoegsel, Achternaam, Email, Telefoon, Geboortedatum, LidmaatschapId, StartDatum, Status, Wachtwoord, AangemaaktOp)
VALUES
    ('Sophie', NULL, 'Bakker', 'sophie.bakker@email.nl', '0612345679', '1992-05-20', 2, '2026-01-15', 'Actief', '$2y$10$SS5Iye51H.xSJSS6NrR6aucNfXoor4P7HoMgUUKChgMnxFAFKH4E.', '2026-01-15 09:00:00'),
    ('Emma', 'van', 'Berg', 'emma.vanberg@email.nl', '0612345680', '1988-08-12', 2, '2026-02-01', 'Actief', '$2y$10$SS5Iye51H.xSJSS6NrR6aucNfXoor4P7HoMgUUKChgMnxFAFKH4E.', '2026-02-01 10:15:00'),
    ('Lisa', NULL, 'Jansen', 'lisa.jansen@email.nl', '0612345681', '1995-11-30', 1, '2026-02-10', 'Actief', '$2y$10$SS5Iye51H.xSJSS6NrR6aucNfXoor4P7HoMgUUKChgMnxFAFKH4E.', '2026-02-10 14:30:00'),
    ('Mila', 'de', 'Jong', 'mila.dejong@email.nl', '0612345682', '1991-03-25', 2, '2026-02-15', 'Actief', '$2y$10$SS5Iye51H.xSJSS6NrR6aucNfXoor4P7HoMgUUKChgMnxFAFKH4E.', '2026-02-15 11:20:00'),
    ('Anna', 'van der', 'Meer', 'anna.vandermeer@email.nl', '0612345683', '1993-07-08', 2, '2026-02-20', 'Actief', '$2y$10$SS5Iye51H.xSJSS6NrR6aucNfXoor4P7HoMgUUKChgMnxFAFKH4E.', '2026-02-20 08:45:00'),
    ('Julia', NULL, 'Visser', 'julia.visser@email.nl', '0612345684', '1989-12-15', 4, '2026-02-25', 'Actief', '$2y$10$SS5Iye51H.xSJSS6NrR6aucNfXoor4P7HoMgUUKChgMnxFAFKH4E.', '2026-02-25 13:10:00'),
    ('Nina', 'van', 'Dijk', 'nina.vandijk@email.nl', '0612345685', '1994-04-18', 2, '2026-03-01', 'Actief', '$2y$10$SS5Iye51H.xSJSS6NrR6aucNfXoor4P7HoMgUUKChgMnxFAFKH4E.', '2026-03-01 09:30:00'),
    ('Laura', NULL, 'Mulder', 'laura.mulder@email.nl', '0612345686', '1990-09-22', 2, '2026-03-05', 'Actief', '$2y$10$SS5Iye51H.xSJSS6NrR6aucNfXoor4P7HoMgUUKChgMnxFAFKH4E.', '2026-03-05 10:00:00'),
    ('Sarah', 'de', 'Boer', 'sarah.deboer@email.nl', '0612345687', '1987-01-14', 2, '2026-03-08', 'Actief', '$2y$10$SS5Iye51H.xSJSS6NrR6aucNfXoor4P7HoMgUUKChgMnxFAFKH4E.', '2026-03-08 12:15:00'),
    ('Fleur', NULL, 'Smit', 'fleur.smit@email.nl', '0612345688', '1996-06-05', 1, '2026-03-10', 'Actief', '$2y$10$SS5Iye51H.xSJSS6NrR6aucNfXoor4P7HoMgUUKChgMnxFAFKH4E.', '2026-03-10 15:45:00'),
    ('Sanne', 'van', 'Leeuwen', 'sanne.vanleeuwen@email.nl', '0612345689', '1992-10-28', 2, '2026-03-12', 'Actief', '$2y$10$SS5Iye51H.xSJSS6NrR6aucNfXoor4P7HoMgUUKChgMnxFAFKH4E.', '2026-03-12 11:30:00'),
    ('Eva', NULL, 'Hendriks', 'eva.hendriks@email.nl', '0612345690', '1991-02-17', 2, '2026-03-15', 'Actief', '$2y$10$SS5Iye51H.xSJSS6NrR6aucNfXoor4P7HoMgUUKChgMnxFAFKH4E.', '2026-03-15 09:15:00'),
    ('Lotte', 'van den', 'Heuvel', 'lotte.vandenheuvel@email.nl', '0612345691', '1993-08-09', 4, '2026-03-18', 'Actief', '$2y$10$SS5Iye51H.xSJSS6NrR6aucNfXoor4P7HoMgUUKChgMnxFAFKH4E.', '2026-03-18 14:00:00'),
    ('Iris', NULL, 'Peters', 'iris.peters@email.nl', '0612345692', '1989-11-23', 2, '2026-03-20', 'Actief', '$2y$10$SS5Iye51H.xSJSS6NrR6aucNfXoor4P7HoMgUUKChgMnxFAFKH4E.', '2026-03-20 10:30:00'),
    ('Noa', 'de', 'Groot', 'noa.degroot@email.nl', '0612345693', '1994-12-31', 2, '2026-03-22', 'Actief', '$2y$10$SS5Iye51H.xSJSS6NrR6aucNfXoor4P7HoMgUUKChgMnxFAFKH4E.', '2026-03-22 13:45:00');


-- Step : 04.3
/********************************************************************************
-- Doel : Voeg rijen toe aan tabel Reserveringen.
-- ******************************************************************************
-- Versie     Datum          Auteur          Omschrijving
-- ******     **********     **********      **************
-- 01         07-03-2026     Joey            Nieuw
-- 02         09-03-2026     Silvan          Formatting
*********************************************************************************/

-- Test reserveringen voor Jan de Vries
INSERT INTO Reserveringen (AccountId, LesId, Datum, Status)
VALUES
(1, 1, '2026-03-09', 'Bevestigd')
     ,(1, 2, '2026-03-10', 'Bevestigd')
     ,(1, 8, '2026-03-14', 'Wachtlijst');

-- Reserveringen voor Yoga voor beginners (LesId 1) - 15 nieuwe leden
INSERT INTO Reserveringen (AccountId, LesId, Datum, Status)
VALUES
(3, 1, '2026-04-09', 'Bevestigd')
     ,(4, 1, '2026-04-09', 'Bevestigd')
     ,(5, 1, '2026-04-09', 'Bevestigd')
     ,(6, 1, '2026-04-09', 'Bevestigd')
     ,(7, 1, '2026-04-09', 'Bevestigd')
     ,(8, 1, '2026-04-09', 'Bevestigd')
     ,(9, 1, '2026-04-09', 'Bevestigd')
     ,(10, 1, '2026-04-09', 'Bevestigd')
     ,(11, 1, '2026-04-09', 'Bevestigd')
     ,(12, 1, '2026-04-09', 'Bevestigd')
     ,(13, 1, '2026-04-09', 'Bevestigd')
     ,(14, 1, '2026-04-09', 'Bevestigd')
     ,(15, 1, '2026-04-09', 'Bevestigd')
     ,(16, 1, '2026-04-09', 'Bevestigd')
     ,(17, 1, '2026-04-09', 'Bevestigd');


-- Check of alles goed is gegaan
SELECT * FROM Lidmaatschappen;
SELECT * FROM Accounts;
SELECT * FROM Reserveringen;