# sistema_login

Sistema de login completo com página de cadastro de dados, validação de cadastro, recuperação de senha e token de sessão.

# Neste projeto será detalhado como criar um sistema de login completo utilizando a biblioteca phpmailer.

Esta iniciativa decorre da dificuldade encontrada por mim, que sou um programador iniciante, para conseguir implementar um sistema de login com envio de emails para validação de cadastro.
Na realidade, o projeto que demandou este recurso foi um aplicativo chamado safapp que você também pode encontrar aqui nos meus repositórios.
Este aplicativo foi implementado utilizando uma versão antiga do phpmailer que foi descontinuada em razão de falhas de segurança. Desse modo a funcionalidade de envio de emails utilizando aquela versão ficou inutilizada.
Além da dificuldade de implementação da nova versão, tive muita dificuldade em implementar o gmail como provedor para envio dos emails, isso porque houve a necessidade de utilizar um mecanismo de autenticação chamado OAuth. Trarei mais detalhes ao longo desta documentação.
Bom, que fique claro, esta iniciativa é apenas para reforçar o aprendizado e construir um caminho das pedras para novos aventureiros.
A documentação de implementação do phpmailer e de autenticação de emails utilizando o gmail, coisa que você só precisa fazer se você quiser (rs), é bem bacana, mas talvez pela minha inexperiência a dificuldade foi um pouco mais elevada.
Por fim, a ideia é construir um passo a passo para implementação deste recurso e disponibilizar os códigos comentados para treino e consulta de quem se interessar.

OBS: Alguns arquivos para uso do PHPmailer estão neste repositório, mas para utilização em produção será necessário sua instalação completa, pois os arquivos que trago aqui são apenas para referencia etapas do processo e você praticamente não terá quase nenhum trabalho de alterá-los.

# Para visulizar o passo a passo de implementação do phpmailer e do sistema de autenticação do gmail veja phpmailerGmail.md;

# Para visualizar os códigos comentados basta consultar os arquivos presentes na raiz do projeto e o arquivo db/conexao.php;

# Para conferir o resultado do sistema de login em produção é só <a href="https://devat30.com/sistema_login">clicar aqui</a>
