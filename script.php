<?php

//ini_set('mysql.connect_timeout', 300);
//ini_set('default_socket_timeout', 300);

$cliente = 104;

$link = mysqli_connect('localhost:8000', 'root', '12345', 'base_inicial') or die(mysqli_error($link));

$novoLink = mysqli_connect("localhost:8000", "root", "12345", "base_nova") or die (mysqli_error($novoLink));

mysqli_options($link, MYSQLI_OPT_CONNECT_TIMEOUT, 0);
mysqli_options($novoLink, MYSQLI_OPT_CONNECT_TIMEOUT, 0);

function preencheArquivo($query, $table)
{
    global $novoLink;

    if (!mysqli_ping($novoLink)) {

        mysqli_close($novoLink);
      
        $novoLink = mysqli_connect("localhost:8000", "root", "12345", "base_nova") or die (mysqli_error($novoLink));
        
        mysqli_options($novoLink, MYSQLI_OPT_CONNECT_TIMEOUT, 0);
    }

    echo ("\n#\n# Tabela: $table\n#\n\n");

    while ($r = mysqli_fetch_assoc($query)){
        $sql = "INSERT INTO `$table` (`";

        $f = array_keys($r);

        $sql .= implode("`,`", $f) . "`) VALUES (";

        $f = [];
        $t = [];
        $c = 0;
        $values = array_values($r);

        foreach ($r as $a){
            $f[] = '?';

            if(gettype($values[$c]) == "string" || $values[$c] == NULL){
                $t[] = 's';
            }else if(gettype($values[$c]) == "integer"){
                $t[] = 'i';
            }else if(gettype($values[$c]) == "double"){
                $t[] = 'd';
            }else if(gettype($values[$c]) == "boolean"){
                $t[] = 'b';
            }
            
            $c++;
        }
        
        $sql .= implode(",", $f) . ");\n";

        $stmt = mysqli_prepare ($novoLink, $sql);

        mysqli_stmt_bind_param ($stmt, implode("",$t), ...$values);

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

function confereLink()
{
    global $link;

    if (!mysqli_ping($link)){

        mysqli_close($link);

        $link = mysqli_connect('localhost:8000', 'root', '12345', 'base_inicial') or die (mysqli_error($link));
    
        mysqli_options($link, MYSQLI_OPT_CONNECT_TIMEOUT, 0);
    }

    return $link;
}

//Select tabela Pessoa
$query = mysqli_query(confereLink(), "SELECT p.*
FROM Pessoa p
INNER JOIN Cliente c ON c.pessoaIdFK = p.id
WHERE c.id = $cliente");
preencheArquivo($query, 'Pessoa');

//Select tabela Pessoa
$query = mysqli_query(confereLink(), "SELECT p.*
FROM Pessoa p
INNER JOIN Funcionario f ON f.pessoaIdFK = p.id
WHERE f.clienteIdFK = $cliente");
preencheArquivo($query, 'Pessoa');

//Select tabela PessoaJuridica
$query = mysqli_query(confereLink(), "SELECT pj.*
FROM PessoaJuridica pj
INNER JOIN Fornecedor f ON f.pessoaIdFK = pj.pessoaIdFK
WHERE f.clienteIdFK = $cliente");
preencheArquivo($query, 'PessoaJuridica');

//Select tabela PessoaFisica
$query = mysqli_query(confereLink(), "SELECT pf.*
FROM PessoaFisica pf 
INNER JOIN Funcionario f ON f.pessoaIdFK = pf.pessoaIdFK 
WHERE f.clienteIdFK = $cliente");
preencheArquivo($query, 'PessoaFisica');

//Select tabela Cliente
$query = mysqli_query(confereLink(), "SELECT * FROM Cliente WHERE id = $cliente");
preencheArquivo($query, 'Cliente');

//Select tabela Fornecedor
$query = mysqli_query(confereLink(), "SELECT * FROM Fornecedor WHERE clienteIdFK = $cliente");
preencheArquivo($query, 'Fornecedor');

//Select tabela Funcionario
$query = mysqli_query(confereLink(), "SELECT * FROM Funcionario WHERE clienteIdFK = $cliente");
preencheArquivo($query, 'Funcionario');

//Select tabela Usuario
$query = mysqli_query(confereLink(), "SELECT * FROM Usuario WHERE clienteIdFK = $cliente");
preencheArquivo($query, 'Usuario');

//Select tabela Movimento
$query = mysqli_query(confereLink(), "SELECT * FROM Movimento WHERE clienteIdFK = $cliente");
preencheArquivo($query, 'Movimento');
