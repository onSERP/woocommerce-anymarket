<?php

/**
 * format cpf and cnpj
 *
 * @param string $value
 * @return void
 *
 * @author David Alves <contato@davidalves1.com>
 * @see https://gist.github.com/davidalves1/3c98ef866bad4aba3987e7671e404c1e
 *
 */
function anymarket_formatCnpjCpf( $value ){
  $cnpj_cpf = preg_replace("/\D/", '', $value);

  if (strlen($cnpj_cpf) === 11) {
    return preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "\$1.\$2.\$3-\$4", $cnpj_cpf);
  }

  return preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "\$1.\$2.\$3/\$4-\$5", $cnpj_cpf);
}
