Partakideak:
- Uriel Martin
- Oihan Torrontegi

Deskribapena:
Informazio Sistemen Segurtasuna Kudeatzeko Sistemak irakasgairako garatutako 
Web Sistema proiektua. Repo honek Dockerrekin zabaltzeko aplikazioa eta 
jarraibideak ditu.

Baldintzak
- Docker
- Docker Compose


1. Repositorioa klonatu
   git clone https://github.com/urielmartinn/SEGUR.git
   cd REPO_IZENA

3. Entregaren adarrera aldatzeko:
   git checkout zeregina_1

4. Sortu eta abiarazi
   docker-compose up --build -d

5. Nabigatzailetik sartzeko:
   http://localhost:81/

Rpositorioaren edukia: 
- docker-compose.yml: orquesta apache/php eta mariadb.
- Dockerfile:
- www/: PHP/HTML/JS/CSS kodea.
- schema.sql: datu basea eta bere taulak sortzeko script-a
- USO.pdf: jarraibideak 

Harremanetan jartzeko:
- umartin025@ikasle.ehu.eus
- otorrontegui003@ikasle.ehu.eus
