<?php
namespace App\Dto;

use App\Entity\CommentFile;
use Symfony\Component\Validator\Constraints as Assert;


/**
 *
 * @author Etudiant
 *        
 */
class FileDto extends CommentFile
{
    
    // le fait de faire un assert file assure déjà que le fichier existe
    // on va fixer la taille du fichier
    
    /**
     * 
     * @Assert\File(
     *      maxSize = "2M"
     * )
     */
    public $file;
}

