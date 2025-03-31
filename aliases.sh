# si ca ne fonctionne pas: 
# nano ~/.bashrc

# load_aliases() {
#     if [ -f "$(pwd)/aliases.sh" ]; then
#         . "$(pwd)/aliases.sh"
#     fi
# }

# # Appeler la fonction chaque fois que le répertoire est changé
# cd() {
#     builtin cd "$@" && load_aliases
# }

# # Charger les alias au démarrage du shell si le fichier existe dans le répertoire actuel
# load_aliases

#Puis: source ~/.bashrc

# alias pour installer une librairie composer
alias ccomposer='docker compose run --rm $(docker ps --format '{{.Names}}' | grep apache) composer'
# alias pour utiliser le wizard symfony
alias cconsole='docker compose run --rm $(docker ps --format '{{.Names}}' | grep apache) symfony console'
# alias pour entrer dans le container npm
alias nnpm='docker compose exec $(docker ps --format '{{.Names}}' | grep apache) bash'

alias s777='sudo chmod 777 -R ./'
# alias de console symfony
alias me='docker compose run --rm $(docker ps --format '{{.Names}}' | grep apache) symfony console make:entity'
alias mm='docker compose run --rm $(docker ps --format '{{.Names}}' | grep apache) symfony console make:migration'
alias dmm='docker compose run --rm $(docker ps --format '{{.Names}}' | grep apache) symfony console d:m:m'
alias dfl='docker compose run --rm $(docker ps --format '{{.Names}}' | grep apache) symfony console d:f:l'
alias ddd='docker compose run --rm $(docker ps --format '{{.Names}}' | grep apache) symfony console d:d:d --force'
alias ddc='docker compose run --rm $(docker ps --format '{{.Names}}' | grep apache) symfony console d:d:c'
alias ccc='docker compose run --rm $(docker ps --format '{{.Names}}' | grep apache) symfony console cache:clear'

# alias pour exporter un snap de la base de données
alias db-export='sudo docker exec $(docker ps --format '{{.Names}}' | grep mariadb) /root/backup.sh'
# alias pour importer un snap de la base de données
alias db-import='sudo docker exec $(docker ps --format '{{.Names}}' | grep mariadb) /root/restore.sh'