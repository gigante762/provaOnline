## Provas Online

Projeto para realizar provas online. Onde temos professores, turmas, provas e alunos.

O professor monta a prova, o aluno realiza e o sistema corrige as provas.

Provas com data de abertura, fechamento e tempo de realização.

Fazer o sistema de provas, onde a prova será gerada por um json com markdown, como estrutura de dados.

# Técnicos a fazer

Implementar a função do professor enviar a prova para os alunos. (não farei validação para não permitir o professor editar um provar que já foi lançada.)

    Cada aluno vai pegar as suas avalições disponíveis para realizar tipo `$user->examsAvailables()` e dai isso será jogado para um view para então no momento que ele acessar o exame, disparar o inicio da prova e todo o restante.

    - [ ] Quando o aluno abrir a prova setar tempo de abertura
    - [ ] Uma vez aberta a prova as regras do tempo devem valer
    - [ ] Prova disponível para ver apenas dentro do prazo estipulado.
    - [ ] Caso 


    Quando o aluno clicar em 'iniciar exame' disparar um post que vai setar o inicio do exame. (seria interessante fazer um autosave de cada resposta, caso o tempo acabe alternativas marcadas já estariam salvas, talvez usando livewire para fazer o bind)

    Para o professor ele vai pegar todos os exames também, e a `show` de cada turma mostrará os alunos que já fizeram a prova e os alunos que ainda não entregaram.

As respostas da prova serão enviadas e recebidas como um json salvo no banco de dados assim, esse json com as respostas será, enviado para um job para processar todos os exames iguais.