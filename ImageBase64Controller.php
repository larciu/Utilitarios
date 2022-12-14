<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageBase64Controller extends Controller
{
    #extrai a extensão da imagem base64
    public static function getB64Type($str) {
        return substr($str, 11, strpos($str, ';') - 11);
    }

    #função que extrai a url da imagem apartir de um html que contenha tag img
    #caso seja em base64, transforma em binário, salva no servidor e substiui o base 64 pela url do servidor no html.
    #não importar quantas tags imgs tenham, elas serão modificadas e upadas.
    #passe por parametros os arrays com os htmls e também o nome dos campos que contém esse html bem.
    #como parametros opcional, temos o nome da pasta onde esse binário, como default questoes
    public static function uploads64FromString ($htmls, $camposImagens, $path="questoes") {
        foreach ($camposImagens as $camposImagen){
            $d = $htmls[$camposImagen];
            if (!$d){
                continue;
            }
            preg_match_all( '@src="([^"]+)"@' , $d, $match);
            $images = array_pop($match);
            foreach ($images as $img){
                if (!preg_match('/base64/', $img)){
                    continue;
                }
                $extension = ImageBase64Controller::getB64Type($img);
                $imagem_atual = str_replace("data:image/$extension;base64,", '', $img);
                $imagem_atual = str_replace(' ', '+', $imagem_atual);
                $data_atual = date('Y').'/'.date('m').'/'.date('d')."/";

                $nameFile = uniqid(date('HisYmd')) . ".$extension";
                $caminhoArquivo = env('APP_URL') . "/storage/questoes/$data_atual" . $nameFile;

                Storage::put( "\public\\$path\\$data_atual". $nameFile, base64_decode($imagem_atual));

                $htmls[$camposImagen] = str_replace($img, $caminhoArquivo, $d);

            }
        }

        return $htmls;
    }
}
