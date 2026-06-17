# Rya Bakery

Gestionale e sito pubblico per Rya Bakery

Il progetto e diviso in due cartelle:

- `Rya-Bakery-Backend`: backend Laravel
- `Rya-bakery-frontend`: frontend React 

## Stack

- Backend/Admin: Laravel, PHP, Laravel Breeze, Blade, SCSS
- Frontend pubblico: React, CSS, Vite
- Database: MySQL

## Frontend pubblico

Il frontend React consente al cliente di:

- navigare pagine separate per Home, Prodotti, Dettaglio prodotto, Carrello, Checkout e Conferma ordine;
- consultare catalogo e categorie prodotto;
- vedere disponibilita e prezzi;
- aggiungere prodotti al carrello;
- modificare quantita;
- inserire nome cliente, numero tavolo e note;
- confermare un ordine;
- ricevere messaggi chiari di conferma o errore.


## Backend amministrativo

Funzionalita disponibili:

- dashboard con ordini totali, stati, prodotti e incasso accettato;
- sidebar gestionale con Dashboard, Prodotti, Ordini e Storico ordini;
- profilo utente accessibile solo dal menu utente;
- CRUD prodotti con upload immagine, prezzo, categoria, disponibilita e stato attivo;
- gestione ordini con stato ricevuto, in lavorazione, consegnato o annullato;
- storico ordini con ripristino entro 30 minuti per gli annullati;
- completamento/consegna ordine e archiviazione nello storico dopo 10 minuti;
- modifica dati cliente, tavolo, note, prodotti e quantita ordine.




## Sicurezza
- Le rotte admin sono protette da autenticazione.
- Upload immagini limitato a `jpg`, `jpeg`, `png`, `webp` e massimo 2 MB.
- Mass assignment limitato sui modelli tramite attributi `Fillable`.
- Gli ID restano interni al database; prodotti e ordini usano slug verso l'esterno.
- La struttura API e pronta per aggiungere Laravel Broadcasting/Reverb per aggiornamenti real-time degli ordini.

## Autore

- Montella Antimo

Progetto d'allenamento ispirato a Rya Bakery, La mia focacceria ligure preferita.
