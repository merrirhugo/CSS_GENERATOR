<?php
$spritename = "sprite.png"; //Variable permettant de définir le nom de notre sprite.
$stylename = "style.css"; //Variable permettant de définir le nom de notre stylesheet.
$arrayfiles = []; //Tableau qui stocke les chemins des images.
$arraywidth = []; //Tableau qui stocke les largeurs des images.
$arrayheight = []; //Tableau qui stocke la hauteur des images.
$HORIZONTALS = 0; //Variable permettant de définir la position des images.
$recursive = false; //Variable permettant de gérer la recursivité.

//Fonction permettant d'afficher le manuel de css_generator.php

function man()
    {
        echo "
CSS_GENERATOR(1) UserCommands CSS_GENERATOR(1)

NAME

css_generator - sprite generator for HTML use

SYNOPSIS

css_generator [OPTIONS]. . . assets_folder

DESCRIPTION

Concatenate all images inside a folder in one sprite and write a style sheet ready to use. Mandatory arguments to long options are mandatory for short options too.

-r, --recursive
Look for images into the assets_folder passed as arguement and all of its subdirectories.

-i, --output-image=IMAGE
Name of the generated image. If blank, the default name is « sprite.png ».

-s, --output-style=STYLE
Name of the generated stylesheet. If blank, the default name is « style.css ».\n";
                        
}

// Si l'utilisateur ne rentre aucun dossier ni aucune option

if($argc == 1)
{
    echo "Veuillez entrer le nom de votre dossier après les commandes désirées présentes dans le manuel.\n(Pour afficher le manuel de la fonction css_generator.php merci d'entrer dans le terminal :\nphp css_generator.php man (ou) php css_generator.php -h\n";
}
else
{
    //Boucle permettant de parcourir l'ensemble de mes arguments
    for($i=1; $i < $argc; $i++)
    {
        global $recursive;
        global $spritename;

        if($argv[$i] == "man" || $argv[$i] == "-h")
        {
            man(); //Afficher le (man)
        }
        elseif(str_contains($argv[$i], "-i=") || str_contains($argv[$i], "--output-image="))
        {
            $spritename = substr($argv[$i], strpos($argv[$i], "=") + 1); //Changer le nom du sprite
            echo "Le nom de votre sprite a bien été enregistré.\n";

            if(!str_contains($spritename, ".png"))
            {
                $spritename .= ".png"; //Rajoute automatiquement la bonne extension
            }  
        }
        elseif(str_contains($argv[$i], "-s=")|| str_contains($argv[$i], "--output-style="))
        {
            $stylename = substr($argv[$i], strpos($argv[$i], "=") + 1); //Changer le nom du stylesheet
            echo "Le nom de votre fichier css a bien été enregistré.\n";
            
            if(!str_contains($stylename, ".css"))
            {
                $stylename .= ".css"; // Rajoute automatiquement la bonne extension

            }  
        }
        elseif($argv[$i] == "-r" || $argv[$i] == "--recursive")
        {
            $recursive = true; //Active la Récursivité
            echo "Recursivité Activé\n";
        }
        elseif(is_dir($argv[$i]))
        {
            scan($argv[$i]); //Permet de scanner l'argument dossier
        }
        else
        {
            echo "Merci de rentrer une commande valide !"; //Gestion d'erreur
        }
        
    }
}

//Reproduire la fonction scandir 

function scan($folder)
{
    global $arrayfiles, $recursive;
    
        if ($content = opendir($folder)) 
        {
            while (($file = readdir($content)) !== false)  
            {
                if($file != "." && $file != "..")
                {
                    /* permet de stocker le chemin véritable de chaque image contenu dans 
                    le dossier */
                    $path = realpath($folder.DIRECTORY_SEPARATOR.$file);
                    
                    // permet de ne garder que l'extension png 
                    if(substr($path, -3) == "png")
                    {
                        usleep(500000);
                        echo "fichier : " . $file . "\n";
                        array_push($arrayfiles, $path); //Stocke les chemins d'images dans un tableau
                    }
                    elseif(is_dir($path) && $recursive == true)
                    {
                        //permet la recursivité
                        scan($path);
                    }
                    else
                    {
                        echo "";
                    }
                }
            }
            closedir($content);
        }
}

//Si il y a au moins une image dans notre dossier génére le sprite
if(array_key_exists(1, $arrayfiles)){
    generate_sprite($arrayfiles);
}
           

/*Génerer une sprite contenant les images récupérées dans le dossier à
l'issu de la fonction scan */

function generate_sprite($arrayfiles)
        {
            global $arraywidth;
            global $arrayheight;
            global $HORIZONTALS;
            global $spritename;
            global $stylename;

            //Récuperer chaque chemin pour obtenir la largeur et la hauteur

            foreach($arrayfiles as $file)
            {
                list($width, $height) = getimagesize($file);
                array_push($arraywidth, $width);
                array_push($arrayheight,$height);
                
            }

            //Dimension de notre background

            $heightmax = max($arrayheight);
            $widthmax = array_sum($arraywidth);

            //Permet à l'utilisateur de choisir l'axe du sprite.

            $input = readline("Veuillez choisir l'axe du sprite (1 = VERTICAL, 2 = HORIZONTAL): \n");
            
            //Choix sprite horizontal

            if($input == "2")
            {
                //permet de créer une image qui contiendra toutes les autres

                $backgroundHOR = imagecreatetruecolor($widthmax, $heightmax);

                foreach($arrayfiles as $image)
                {

                /*Ici chaque chemin va permettre de creer une image au format png 
                stocké au sein d'une variable.*/

                    $tmp = imagecreatefrompng($image);

                /*permet de récuperer la largeur et la hauteur de chaque image afin de 
                parametrer la fonction imagecopy qui permet de copier chaque image crée 
                au sein de notre image conteneur.*/

                    list($width, $height) = getimagesize($image); 
                    imagecopy($backgroundHOR, $tmp, $HORIZONTALS, 0, 0, 0, $width, $height);
                    
                /*permet de gérer les coordonées de destination de chaque image. L'ordre 
                est important, on incrémente la variable à la suite d'imagecopy pas avant
                afin de commencer à 0 pour la première image.*/
                    
                    $HORIZONTALS += $width;
                
                }
            
            imagepng($backgroundHOR, $spritename); //Crée notre image finale
            imagedestroy($tmp);
            usleep(500000);
            echo $spritename . " en position horizontale a bien été crée !\n";

            $css = ("
.sprite {
    background-image: url($spritename);
    background-repeat: no-repeat;
    display: block;
}");
            $X = 0;
            
            
                foreach($arrayfiles as $key => $image)
                {
                    list($width, $height) = getimagesize($image);

                    $css1 = ("
.sprite-$key {
    width:  $width" . "px" . ";
    height: $height" . "px" . ";
    background-position: $X" . "px" ."0px;
    }");
                    $css .= $css1;
                    $X += $width;
                    $key++;
                    file_put_contents($stylename, $css); //Crée notre stylesheet


                        
                }
                usleep(500000);
                echo $stylename . " a bien été crée !\n";

            }

            //Option sprite verticale

            elseif ($input == "1")
            {
                $heightmax = array_sum($arrayheight);
                $widthmax = max($arraywidth);

                $backgroundVER = imagecreatetruecolor($widthmax, $heightmax);

                foreach($arrayfiles as $image)
                {
                    $tmp = imagecreatefrompng($image);
                    list($width, $height) = getimagesize($image); 

                    /*Cette fois ci il faut changer l'axe y dans imagecopy pour que $HORIZONTALS 
                    incrémente chaque image les unes en dessous des autres.*/
                    imagecopy($backgroundVER, $tmp, 0, $HORIZONTALS, 0, 0, $width, $height);
                    $HORIZONTALS += $width;
                
                }
            
            imagepng($backgroundVER, $spritename);
            imagedestroy($tmp);
            usleep(500000);
            echo $spritename . " en position verticale a bien été crée !\n";


            $css = ("
.sprite {
    background-image: url($spritename);
    background-repeat: no-repeat;
    display: block;
}");
            $Y = 0;


                foreach($arrayfiles as $key => $image)
                    {
                        list($width, $height) = getimagesize($image);

                    $css1 = ("
.sprite-$key {
    width:  $width" . "px" . ";
    height: $height" . "px" . ";
    background-position: 0px" . "$Y " . "px;
}");
                    $css .= $css1;
                    $Y += $height;
                    $key++;
                    file_put_contents($stylename, $css);
                            
                    }
                usleep(500000);
                echo $stylename . " a bien été crée !\n";
            }
            else
            {
                echo "Une erreur s'est produite, merci de réessayer !\n";
                generate_sprite($arrayfiles); //Gestion d'erreurs
            }

        } 
    






