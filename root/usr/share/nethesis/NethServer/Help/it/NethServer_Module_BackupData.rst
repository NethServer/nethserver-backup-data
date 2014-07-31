======
Backup
======

Il backup contiene tutti i dati, come le home degli utenti, le
cartelle condivise, le email ma anche tutte le configurazioni di
sistema. Viene eseguito quotidianamente e può essere completo o
incrementale, in base al giorno della settimana e alla configurazione. I
supporti disponibili per il backup sono: disco USB, condivisione Windows
e condivisione NFS. Al termine del backup è possibile inviare via e-mail
una notifica all'amministratore o ad un indirizzo personalizzato.

Generale
========

Abilita backup automatico
    L'opzione abilita o disabilita la procedura di backup. Di default è *abilitato*.
Orario backup
    Indica l'orario in cui verrà eseguito il backup. E' possibile modificare il parametro intervenendo direttamente sul campo.
Full
    Selezionando questa opzione verrà eseguito un backup completo tutti i giorni della settimana
Incrementale
    Selezionando questa opzione verrà eseguito un backup full nel giorno
    selezionato attraverso il campo specifico mentre il resto della
    settimana verrà eseguito un backup incrementale.
Politica di conservazione
    Specifica il numero di giorni per i quali verranno conservati i backup.

Destinazione
============

Disco USB
    Seleziona come destinazione del backup un disco USB. Il disco USB deve
    essere formattato in filesystem supportato (ext2/3/4 o FAT, NTFS non è supportato) ed una label configurata.

    * Etichetta filesystem: Vengono elencati i dischi USB collegati
      Se il disco è montato (occupato), viene marcato con una "M". 

Condivisione Windows (CIFS)
    Seleziona come destinazione del backup una condivisione Windows (CIFS). L'autenticazione è obbligatoria.

    * Server: indirizzo IP o FQDN del server Windows di destinazione
    * Condivisione: il nome della condivisione Windows di destinazione
    * Utente: utente da utilizzare per l'autenticazione
    * Password: password da utilizzare per l'autenticazione.
Condivisione NFS
    Seleziona come destinazione del backup una condivisione NFS
Host
   L'indirizzo IP o FQDN del server NFS

   * Condivisione: nome della condivisione NFS di destinazione

Notifiche
=========

In caso di errore
    Invia notifica solo in caso di fallimento del backup.
Sempre
    Verrà sempre inviata una notifica, sia in caso di successo che in caso di fallimento.
Mai
    Non verrà inviata alcuna notifica.
Invia notifica a
    Indica a chi verrà inviata l'e-mail di notifica
   
    * Amministratore di sistema: la notifica del backup verrà inviata alla mail dell'amministratore di sistema (utente admin)
    * Indirizzo personalizzato: la notifica del backup verrà inviata ad un indirizzo mail personalizzato

