<?php
include '../class/template.class.php';
//We choose to use the template example that is in the directory skins (default directory)
$tpl = new Template('example');
//Prepare the array for the menu
$menu = array(array('name' => 'Home', 'link' => 'index.php'), array('name' => 'Contacts', 'link' => 'index.php?mode=contacts'));
//Assign "menu" to the template with the array
$tpl->assign('menu', $menu);
//array for the blog
$blog = array(
    array(
        'image' => 'templatemo_image_01.jpg',
        'date' => 'May 29th',
        'category' => 'Website Templates',
        'author' => 'Templatemo.com',
        'title' => 'Vestibulum ante ipsum',
        'content' => 'Duis convallis mauris a sapien tempor blandit. Morbi commodo, velit non hendrerit porta, tellus enim commodo massa, vitae tempor dolor tortor in enim. In id libero purus, eleifend varius lacus. Phasellus elementum pellentesque hendrerit. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.',
        'comments' => '120'
    ),array(
        'image' => 'templatemo_image_02.jpg',
        'date' => 'May 28th',
        'category' => 'Website Templates',
        'author' => 'Templatemo.com',
        'title' => 'Pellentesque quis lacus',
        'content' => 'Susce consequat, erat vel vulputate malesuada, ipsum ligula porttitor purus, eget porta odio diam eget tellus. Nulla tincidunt auctor justo non interdum. Cras sed diam nisi. Integer quis purus augue. Suspendisse placerat vestibulum suscipit. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia.',
        'comments' => '250'
    ),
);
//Assign "blog"
$tpl->assign('blog', $blog);
//We decide to MAKE the page, he will compile the file "blog" with the extension selected "html" 
//so, blog.html and it will be processed and compiled.
$tpl->burn('blogtest', 'html');
?>