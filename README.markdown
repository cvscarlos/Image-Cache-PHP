Image Class
===========
Classe em PHP para fazer cache de uma imagem em tamanho menor.
A imagem pode ser obtida através de uma url externa ou dentro do próprio domínio.

    // Definindo o local físico do cache e a URL até ele
    $img=new ImageCache("user/html/cache","/images/cache");
    
    // Exemplo imagem redimensionada p/ 80 x 80 (cortada se necessário)
    <img src="<?php $img->printUrl('http://www.exemple.com/img.jpg',72,80,80,true);?>" alt="imagem" />
    
    // Exemplo imagem em tamanho original
    <img src="<?php $img->printUrl('http://www.exemple.com/img.jpg');?>" alt="imagem" />