FROM node:13

# install process manager
RUN npm install pm2@latest -g

# EXPOSE 9000

#RUN groupadd -r nodejs && useradd -m -r -g nodejs nodejs

#USER nodejs

WORKDIR /var/project

# Bundle app source
COPY . .

# Following lines are finnicky on osx
#WORKDIR /var/project/backend
#RUN npm install
#WORKDIR /var/project/frontend
#RUN npm install
#WORKDIR /var/project

# CMD ["pm2-runtime", "process.yml"]

# Dev purposes
ENTRYPOINT ["tail", "-f", "/dev/null"]