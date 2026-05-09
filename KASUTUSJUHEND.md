# Fototeek: kasutusjuhend

## 1. Mis on Fototeek?

**Fototeek** on veebipõhine perearhiiv: saad luua **albumeid**, lisada **pilte (mälestusi)** koos teksti ja kontekstiga ning **jagada albumeid** teiste registreerunud kasutajatega. Jagatud albumil saab kaastööline pilte vaadata ja lisada vastavalt õigustele.

Rakendust kasutad **veebibrauseris** (arvuti või nutiseade). Tööks on vaja internetiühendust.

---

## 2. Konto loomine ja sisselogimine

### Registreerumine

1. Ava rakenduse avaleht.
2. Klõpsa **„Registreeru“** (või avalehel **„Alusta oma arhiivi“**, kui sa pole veel sisse loginud).
3. Täida vorm ja lõpeta registreerumine.

### Sisselogimine

1. Ava leht **„Logi sisse“** (`/logi-sisse`).
2. Sisesta e-post ja salasõna ning logi sisse.

Pärast sisselogimist salvestatakse seansile vajalik teave brauseris (token). **Ühisel või avalikul arvutil** logi peale töö lõppu kindlasti **välja**.

---

## 3. Põhinavigatsioon

Pärast sisselogimist on päises paremal menüü (**kolm punkti**):

- **Minu albumid**: albumite loend (`/albumid`).
- **Kasutaja seaded**: profiil, salasõna, konto kustutamine (`/kasutaja-seaded`).
- **Logi välja**: lõpetab sessiooni.

Logo **„Fototeek“** viib avalehele (`/`).

---

## 4. Albumite loend („Sinu pärand“)

Lehekülg **„Minu albumid“** (`/albumid`) kuvab kõik sinu albumid ja albumid, mis on **sinuga jagatud**.

### Uue albumi loomine

- Klõpsa **„Loo uus album“**. Rakendus loob uue albumi vaikimisi nimega (nt „Uus album 1“).

### Albumi nime muutmine

- Omaniku albumitel on valik **„Muuda nime“**. Sisesta dialoogiaknasse uus nimi.

### Albumi kustutamine

- Omaniku albumitel on **„Kustuta“**. Kinnituse järel album kustutatakse.

### Albumi avamine

- Klõpsa albumi kaardil (polaroid-stiilis), et avada selle albumi sisu.

**Märge „Jagatud sinuga“** näitab, et album kuulub teisele kasutajale, aga sul on ligipääs.

Lehe jaluses on lingid lehtedele **Meist**, **Privaatsus**, **Eetika**.

---

## 5. Üks album (piltide võrk)

Album avaneb aadressilt `/album?albumId=…` (albumi kaardilt klõpsates tehakse see automaatselt).

### Mida siin näed

- Albumi **pealkiri** ja kõik selle albumi **mälestused** (pisipiltidena).
- **Otsing**: saad filtreerida mälestusi pealkirja või „kes / kus“ teksti järgi.
- **„Laadi veel“**: kui pilte on palju, laaditakse järgmine portsjon.

### Uue mälestuse (pildi kirje) lisamine

- Klõpsa **„Lisa pilt“**. Luuakse uus mälestus ja avatakse selle **detailvaade**.

### Mälestuse kustutamine

- Kasutusel on **pildi kustutamise** voog: rakendus pakub numbritega loendit ja kinnitust: järgi ekraanil kuvatavaid juhiseid.

### Lemmik

- Kui õigused lubavad, saab mälestust **lemmikuks** märkida (tärn).

### Galerii

- Kui albumis on pilte, saab avada **täisekraani galerii** ja liikuda **nooleklahvidega** (vasak/parem), sulgemiseks **Esc**.

### Albumi jagamine (ainult omanikul)

1. Sisesta väljale **teise kasutaja e-post** (see isik peab juba olema Fototeegis registreerunud).
2. Kinnita jagamine. Kaastööliseks määratakse roll, mis lubab albumisse pilte lisada ja muuta (**editor**).
3. Kasutajate nimekirjas näed jagatud isikuid; vajadusel saad **eemaldada** kaastöölise.

### Jagatud albumist lahkumine

- Kui sa ei ole albumi omanik, saad albumivaatest **lahkuda** jagatud albumist (kinnitusega). Omanikku see ei kustuta.

**Märkus.** Kui sul on albumil ainult **vaataja** õigus, ei saa sa pilte lisada ega kustutada: seda määrab server vastavalt sinu rollile.

---

## 6. Üks mälestus (üksiku pildi vaade)

Detailvaade avaneb `/malestus?albumId=…&memoryId=…`.

### Väljad

- **Pealkiri**
- **Lugu**: pikem tekst
- **Kes**: kes on pildil (võid kasutada mitme nime jaotamist komadega)
- **Millal**: kuupäev või tekstiline kirjeldus
- **Kus**: koht (samuti võib komadega jagada)

### Pildi lisamine või vahetamine

- Kasuta failivalikut (nt **„Lisa pilt“**). Sobivad tavaliselt **JPG** ja **PNG** failid.

### Salvestamine

- Muudatused salvestatakse **automaatselt** (pole alati eraldi „Salvesta“ nuppu vaja).

### Näomärgistused

- Rakenduses saab pildil **märkeid** paigutada ja neile nimesid seostada. Täpne klõpsujärjekord sõltub ekraanil kuvatavatest juhistest.

### Tõrked pildi suurusega

- Kui kuvatakse teade, et pilt on **serveri jaoks liiga suur**, vali **väiksem fail** või madalama kvaliteediga pilt.

---

## 7. Kasutaja seaded

Lehekülg **„Kasutaja seaded“** (`/kasutaja-seaded`):

- **Profiil**: muuda **nime** ja **e-posti**, seejärel salvesta.
- **Salasõna**: sisesta **praegune salasõna** ja **uus salasõna** (koos korrusega), seejärel salvesta.
- **Konto kustutamine**: nõuab **praegust salasõna** ja kinnitust; tegevus on **pöördumatu**.

---

## 8. Infolehed (avatud ka ilma sisselogimata)

- **Meist** (`/meist`): rakenduse eesmärk ja kontekst.
- **Privaatsus** (`/privaatsus`): lühike ülevaade andmetest ja token-põhisest sisselogimisest (õppeprojekti kontekstis).
- **Eetika** (`/eetika`): mõistlik käitumine fotode ja mälestustega.

---

## 9. Levinud küsimused ja nõuanded

| Olukord | Soovitus |
|--------|-----------|
| Ei saa albumisse sisse / ei näe jagatud albumit | Kontrolli sisselogimist; jagatud album eeldab, et omanik on sind **e-posti järgi** kutsunud ja sul on konto. |
| Ei näe „Lisa pilt“ või ei saa muuta | Sul võib olla albumil ainult **vaataja** roll või sa pole sisse loginud. |
| Pilt ei lähe üles | Proovi väiksemat faili; kontrolli ühendust; loe ekraanil olevat veateadet. |
| Ühisarvuti | **Logi välja** peale kasutamist. |

---

## 10. Tehniline märkus

Rakendus on **üheleherakendus**: mõnikord aitab probleemi korral **lehte värskendada**. Kui midagi ei tööta, proovi teist brauserit. Saidi andmete tühjendamine eemaldab ka sisselogimise: logid siis uuesti sisse.

---

*Dokument kuulub Fototeegi projektile (õppeotstarbeline rakendus).*
