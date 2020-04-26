<?php

function zip($sourcefolder, $file)
{
    $dirlist = new RecursiveDirectoryIterator($sourcefolder);
    $filelist = new RecursiveIteratorIterator($dirlist);
    $zip = new ZipArchive();

    if ($zip->open($file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE)
        die("Não foi possível abrir o arquivo");

    $base = basename($sourcefolder);
    foreach ($filelist as $value) {
        if (is_dir($value) || strpos($value, 'node_modules') > 0)
            continue;
        $path = str_replace('\\', '/', $base . str_replace($sourcefolder, '', $value));
        $zip->addFile($value, $path);
    }

    $zip->close();
}

function build($folder, $xmlfile, $client, $element)
{
    global $argv;
    if (isset($argv[1])) {
        if ($argv[1] != $folder) {
            return;
        }
    }
    echo "Gerando $folder\n";

    $xmlUpdateFile = __DIR__ . '/build/' . $folder . '.xml';
    if (file_exists($xmlUpdateFile)) {
        $xmlUpdate = simplexml_load_file($xmlUpdateFile);
        $versaoAtual = $xmlUpdate->update->version;
    } else {
        $versaoAtual = 0;
    }

    $xml = simplexml_load_file(__DIR__ . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $xmlfile);
    if (version_compare($versaoAtual, $xml->version) == 0) {
        echo "Conteúdo processado sem upgrade de versão.\n";
    }

    foreach (glob(__DIR__ . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . $folder . '-*.zip') as $file) {
        unlink($file);
    }
    $ori = __DIR__ . DIRECTORY_SEPARATOR . $folder;
    $dest = __DIR__ . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . $folder . '.zip';
    zip($ori, $dest);

    file_put_contents($xmlUpdateFile, '<?xml version="1.0" encoding="UTF-8"?>
<updates>
	<update>
		<name>' . $xml->name . '</name>
		<element>' . $element . '</element>
		<type>' . $xml->attributes()->type . '</type>
		<folder>' . $xml->attributes()->group . '</folder>
		<client>' . $client . '</client>
		<version>' . $xml->version . '</version>
		<infourl title="Repositório ' . $xml->name . '">https://github.com/lisandroTSilva/jcomponent</infourl>
        <downloads>
			<downloadurl type="full" format="zip">https://github.com/lisandroTSilva/jcomponent/raw/master/build/' . $folder . '.zip</downloadurl>
		</downloads>
		<targetplatform name="joomla" version="3.[0123456789]"/>
		<tags>
			<tag>stable</tag>
		</tags>
	</update>
</updates>');
    echo "Nova versão gerada\n";
}

build('com_patrimonio', 'patrimonio.xml', 0, 'patrimonio');
build('com_contatomodal', 'contatomodal.xml', 0, 'contatomodal');
build('plg_editors-xtd_contato', 'btn_contato.xml', 0, 'btn_contato');
build('plg_content_contato', 'load_contato.xml', 0, 'load_contato');
