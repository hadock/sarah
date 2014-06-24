sarah
=====
Inteligencia artificial y domótica

ES PRECISO DESTACAR QUE NO DEBEN ABUSAR DEL SCRIPT "MOUTH" DEBIDO A QUE YO MISMO HAGO USO DE UN SERVICIO QUE NO ES MIO
NO PROVOCAR QUE http://www.lumenvox.com/ SE ENTERE QUE ESTE SCRIPT USA SUS SERVIDORES DADO QUE ME FUE DIFICIL DESCUBRIRLOS.

Lo que hace lumevox es proveer un servicio de pago para generar voces naturales a compañias que tienen callcenters y contestadores automatizados.

LO QUE HAGO YO ---> envio un texto a sus servidores especificando la señorita que quiero y luego almaceno el audio para no volver a solicitarlo y lo asocio a la respuesta de Sarah.

index.php -> contiene un html basico que debe ser abierto en Chrome dado que utiliza una característica que solo este podria procesar "SpeechRecognition"

sara.php -> recibe por GET o POST el comando por voz traducido a texto

test.php -> es la primera version y esta siendo reutilizado para la aplicacion de android

service.php -> debe correr como demonio aparte y se encarga de ejecutar los commandos en consola resultados del procesamiento de sara.php


lhpins.pyc -> Libreria escrita en python que permite encender/apagar y obtener el estado los Pins de la PCDuino
        Ejemplo Ecendido/Apagado: python lhpins.pyc --action set --pin 10 --status on|off
        Ejemplo Lectura: python lhpins.pyc --action get --pin 10






