# symf_docker
Create project:
<ul>
  <li>docker exec www_docker_symfony composer create-project symfony/website-skeleton project</li>
  <li>sudo chown -R $USER ./</li>
</ul>
<p>change file .env :</p>
<ul>
  <li> DATABASE_URL=mysql://root:@db_docker_symfony:3306/db_name?serverVersion=5.7</li>
  <li>docker exec -it www_docker_symfony bash</li>
  <li>cd project</li>
  <li>php bin/console doctrine:database:create</li>
</ul>


 




<a href="http://localhost:8741">symfony: http://localhost:8741</a>
<a href="http://localhost:8080">phpmyadmin:http://localhost:8080</a>

