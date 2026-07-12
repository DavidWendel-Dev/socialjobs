<?php

declare(strict_types=1);

namespace Database\Seeders\SkillsData;

/**
 * Pack 1 — 5 testes essenciais de escritório/estudo.
 * Excel, Word, Português, Inglês Básico, Matemática Básica.
 *
 * Estrutura de cada questão: [enunciado, [4 opções], índice correto (0-3), explicação].
 */
final class SkillPack1
{
    public static function data(): array
    {
        return [
            /* ============================================================
             |  1) EXCEL BÁSICO
             |============================================================ */
            [
                'title' => 'Excel Básico',
                'slug'  => 'excel-basico',
                'category' => 'Ferramentas de Escritório',
                'short_description' => 'Fórmulas essenciais, formatação de células e gráficos simples.',
                'description' => 'Demonstre domínio das operações mais comuns de Excel — fórmulas SOMA, MÉDIA e PROCV, referências absolutas ($) e formatação básica.',
                'difficulty' => 'basic',
                'icon' => 'sparkles', 'color' => 'brand',
                'questions' => [
                    ['Qual fórmula soma os valores das células A1 até A10?',
                        ['=SOMA(A1;A10)', '=SOMA(A1:A10)', '=ADICIONAR(A1:A10)', '=A1+A10'], 1,
                        'O operador ":" indica intervalo. "SOMA(A1:A10)" soma tudo entre A1 e A10.'],
                    ['O que significa o símbolo $ em uma referência como $A$1?',
                        ['Formatação em moeda', 'Referência absoluta (não muda ao copiar)', 'Célula com fórmula', 'Erro na fórmula'], 1,
                        'O cifrão fixa a linha e/ou coluna quando a fórmula é copiada.'],
                    ['Qual atalho aplica negrito à célula selecionada?',
                        ['Ctrl+B', 'Ctrl+N', 'Ctrl+I', 'Ctrl+U'], 0,
                        'Ctrl+B (Bold) aplica negrito. Ctrl+I é itálico, Ctrl+U é sublinhado.'],
                    ['Como calcular a média dos valores em B2:B20?',
                        ['=MEDIA(B2:B20)', '=MÉDIA(B2:B20)', '=AVG(B2:B20)', '=MEAN(B2:B20)'], 1,
                        'No Excel em português a função é MÉDIA (com acento). Em inglês é AVERAGE.'],
                    ['Qual função retorna o maior valor de um intervalo?',
                        ['=MAIOR()', '=MÁX()', '=TOP()', '=SUPREMO()'], 1,
                        'MÁX (ou MAX em inglês) retorna o maior valor. MAIOR pega o n-ésimo maior.'],
                    ['Ao arrastar a alça de preenchimento com "Jan" na primeira célula, o Excel:',
                        ['Repete "Jan" em todas', 'Preenche Fev, Mar, Abr...', 'Dá erro', 'Apaga o conteúdo'], 1,
                        'O Excel reconhece sequências como meses e completa automaticamente.'],
                    ['Como referenciar a célula A1 da planilha "Vendas"?',
                        ['Vendas.A1', 'Vendas!A1', 'Vendas:A1', '[Vendas]A1'], 1,
                        'A sintaxe é NomeDaPlanilha!Célula. Use apóstrofos se o nome tiver espaços.'],
                    ['O que retorna =SE(A1>10;"Alto";"Baixo") quando A1=15?',
                        ['15', 'Alto', 'Baixo', 'ERRO'], 1,
                        'A função SE testa: se A1>10 é verdadeiro (15>10), retorna "Alto".'],
                    ['Qual o resultado de =CONT.NÚM(A1:A5) se A1:A5 tem 3 números e 2 textos?',
                        ['5', '3', '2', '0'], 1,
                        'CONT.NÚM conta apenas células com números. CONT.VALORES contaria as 5.'],
                    ['Para congelar a primeira linha ao rolar a planilha:',
                        ['Formatar > Congelar', 'Exibir > Congelar Painéis > Primeira Linha', 'Ctrl+F', 'Alt+F1'], 1,
                        'A opção fica em Exibir > Congelar Painéis > Congelar Linha Superior.'],
                    ['O erro #DIV/0! aparece quando:',
                        ['Célula está vazia', 'Divisão por zero', 'Fórmula circular', 'Tipo de dado errado'], 1,
                        'Ocorre ao dividir por zero ou por célula vazia.'],
                    ['Qual atalho abre "Salvar Como"?',
                        ['Ctrl+S', 'F12', 'Ctrl+Alt+S', 'Shift+S'], 1,
                        'F12 abre "Salvar Como". Ctrl+S salva no local atual.'],
                    ['A fórmula =CONCATENAR(A1;" ";B1):',
                        ['Soma A1 e B1', 'Junta A1 e B1 com espaço', 'Multiplica', 'Substitui'], 1,
                        'CONCATENAR une textos. O ";" separa os argumentos.'],
                    ['Qual comando insere uma tabela dinâmica?',
                        ['Fórmulas > Tabela', 'Inserir > Tabela Dinâmica', 'Dados > Análise', 'Ctrl+T'], 1,
                        'Tabelas dinâmicas ficam em Inserir > Tabela Dinâmica.'],
                    ['O que faz a função =HOJE()?',
                        ['Retorna a hora atual', 'Retorna a data atual (sem hora)', 'Retorna data e hora', 'Retorna o dia da semana'], 1,
                        'HOJE() retorna a data. AGORA() retorna data + hora.'],
                    ['Como aplicar formatação condicional a valores > 100?',
                        ['Página Inicial > Formatação Condicional', 'Inserir > Formatar', 'Dados > Filtro', 'Fórmulas > Estilo'], 0,
                        'Fica em Página Inicial > Formatação Condicional > Regras de Realce.'],
                    ['Se A1=10 e B1=5, quanto retorna =A1*B1-B1?',
                        ['45', '50', '5', '10'], 0,
                        'Ordem: 10*5=50, depois 50-5=45. Multiplicação vem antes da subtração.'],
                    ['Qual fórmula soma somente as células de A1:A10 que são maiores que 100?',
                        ['=SOMA(A1:A10>100)', '=SOMASE(A1:A10;">100")', '=SE(A1:A10>100)', '=SOMA.SE()'], 1,
                        'SOMASE aplica um critério antes de somar. Sintaxe: SOMASE(intervalo; critério).'],
                    ['O que é uma referência mista como A$1?',
                        ['Erro de fórmula', 'Coluna relativa, linha absoluta', 'Ambas absolutas', 'Ambas relativas'], 1,
                        'O $ antes de 1 fixa a linha; a coluna A muda ao copiar horizontalmente.'],
                    ['Como converter um número para texto com 2 casas decimais?',
                        ['=TEXTO(A1;"0,00")', '=NÚMERO(A1)', '=CONVERT(A1)', '=STRING(A1;2)'], 0,
                        'TEXTO(valor; formato) converte números para texto formatado.'],
                ],
            ],

            /* ============================================================
             |  2) WORD BÁSICO
             |============================================================ */
            [
                'title' => 'Word Básico',
                'slug'  => 'word-basico',
                'category' => 'Ferramentas de Escritório',
                'short_description' => 'Formatação, estilos, tabelas e revisão de documentos.',
                'description' => 'Prove que sabe formatar documentos profissionais no Word: parágrafos, estilos, listas, tabelas, revisão e cabeçalhos.',
                'difficulty' => 'basic',
                'icon' => 'briefcase', 'color' => 'blue',
                'questions' => [
                    ['Qual atalho salva o documento?', ['Ctrl+P', 'Ctrl+S', 'Ctrl+D', 'F1'], 1, 'Ctrl+S salva. Ctrl+P imprime.'],
                    ['Como aplicar negrito ao texto selecionado?', ['Ctrl+N', 'Ctrl+B', 'Ctrl+I', 'Ctrl+U'], 1, 'Ctrl+B (Bold) aplica negrito no Word em qualquer idioma.'],
                    ['Qual atalho copia o texto?', ['Ctrl+X', 'Ctrl+C', 'Ctrl+V', 'Ctrl+Z'], 1, 'Ctrl+C copia, Ctrl+X recorta, Ctrl+V cola.'],
                    ['Como inserir uma quebra de página?', ['Enter várias vezes', 'Ctrl+Enter', 'Shift+Enter', 'Alt+P'], 1, 'Ctrl+Enter insere quebra de página. Shift+Enter é quebra de linha.'],
                    ['Onde ficam os estilos (Título 1, Normal, etc.)?', ['Menu Inserir', 'Página Inicial', 'Layout', 'Revisão'], 1, 'Os estilos estão na aba Página Inicial, no meio da faixa.'],
                    ['Para inserir uma tabela 3x5:', ['Inserir > Tabela', 'Layout > Grade', 'Página Inicial > Tabela', 'Revisão > Grade'], 0, 'Fica em Inserir > Tabela. Você seleciona 3 colunas por 5 linhas.'],
                    ['Qual atalho seleciona todo o documento?', ['Ctrl+A', 'Ctrl+T', 'Ctrl+Shift+A', 'Alt+A'], 0, 'Ctrl+A (All) seleciona todo o conteúdo.'],
                    ['Para verificar ortografia:', ['Alt+F7 ou F7', 'Ctrl+E', 'F3', 'Ctrl+F'], 0, 'F7 abre o verificador. Palavras erradas ficam sublinhadas em vermelho.'],
                    ['Como abrir o "Localizar e Substituir"?', ['Ctrl+F', 'Ctrl+H', 'Ctrl+L', 'Ctrl+R'], 1, 'Ctrl+H abre Substituir. Ctrl+F abre só Localizar.'],
                    ['O que é sumário automático?', ['Índice manual', 'Índice gerado a partir dos títulos com estilo', 'Lista de páginas', 'Resumo do documento'], 1, 'Baseia-se em Título 1, Título 2 etc. Fica em Referências > Sumário.'],
                    ['Para numerar as páginas:', ['Inserir > Número de Página', 'Layout > Página', 'Página Inicial > Numeração', 'Revisão > Comentários'], 0, 'Inserir > Número de Página, escolha topo/rodapé e alinhamento.'],
                    ['O modo "Controlar Alterações" é útil para:', ['Escrever mais rápido', 'Ver quem alterou o quê no documento', 'Corrigir gramática', 'Imprimir revisado'], 1, 'Marca inserções/exclusões com autor. Ideal para revisão colaborativa.'],
                    ['Qual atalho aplica alinhamento centralizado?', ['Ctrl+E', 'Ctrl+C', 'Ctrl+Shift+C', 'Ctrl+J'], 0, 'Ctrl+E centraliza. Ctrl+J justifica, Ctrl+L esquerda, Ctrl+R direita.'],
                    ['Um cabeçalho aparece:', ['Em todas as páginas', 'Só na primeira', 'Só na última', 'Só quando imprimir'], 0, 'O cabeçalho se repete em todas as páginas (pode-se configurar exceções).'],
                    ['Para inserir uma imagem do computador:', ['Inserir > Imagem > Este Dispositivo', 'Layout > Imagem', 'Colar apenas', 'Rev > Imagem'], 0, 'Inserir > Imagem > Este Dispositivo permite escolher um arquivo local.'],
                    ['Espaçamento entre linhas de 1,5 é encontrado em:', ['Página Inicial > Espaçamento entre Linhas', 'Layout > Margens', 'Design > Espaço', 'Inserir > Espaçamento'], 0, 'Fica no grupo Parágrafo da aba Página Inicial.'],
                    ['Qual formato de arquivo é o padrão do Word?', ['.pdf', '.docx', '.txt', '.rtf'], 1, 'DOCX é o formato nativo desde o Word 2007.'],
                    ['Como fazer uma lista com marcadores?', ['Página Inicial > Marcadores', 'Inserir > Lista', 'Layout > Lista', 'Design > Marcadores'], 0, 'O botão de marcadores fica na aba Página Inicial, grupo Parágrafo.'],
                    ['O atalho Ctrl+Z faz:', ['Fecha o Word', 'Desfaz última ação', 'Salva', 'Recorta'], 1, 'Ctrl+Z é Desfazer. Ctrl+Y refaz.'],
                    ['Para colar mantendo somente o texto (sem formatação):', ['Ctrl+V', 'Ctrl+Shift+V', 'Alt+V', 'Ctrl+Alt+V'], 3, 'Ctrl+Alt+V abre "Colar Especial" onde você escolhe "Somente Texto".'],
                ],
            ],

            /* ============================================================
             |  3) LÍNGUA PORTUGUESA
             |============================================================ */
            [
                'title' => 'Língua Portuguesa',
                'slug'  => 'lingua-portuguesa',
                'category' => 'Comunicação',
                'short_description' => 'Ortografia, concordância e uso profissional do português.',
                'description' => 'Regras essenciais de português para comunicação profissional: crase, concordância, ortografia e pontuação.',
                'difficulty' => 'intermediate',
                'icon' => 'academic', 'color' => 'amber',
                'questions' => [
                    ['Qual frase está correta?', ['Vou fazer o relatório amanhã.', 'Vou fazer o relatorio amanhã.', 'Vou faser o relatório amanhã.', 'Vou fazer u relatório amanhã.'], 0, '"Relatório" leva acento; "fazer" com Z.'],
                    ['Assinale a frase com uso correto da crase:', ['Vou à padaria.', 'Vou a padaria.', 'Vou à padeiro.', 'Vou à pé.'], 0, 'Crase = a+a. "Vou à padaria" (fem., que aceita "vou a a padaria"). Antes de masculino ou verbo não há crase.'],
                    ['Qual palavra está grafada corretamente?', ['Excessão', 'Exceção', 'Excerção', 'Escepção'], 1, '"Exceção" — com X e Ç. Cognata: excetuar.'],
                    ['A concordância correta é:', ['Fazem dois anos que trabalho aqui.', 'Faz dois anos que trabalho aqui.', 'Fazem dois ano que trabalho aqui.', 'Faz dois ano que trabalho aqui.'], 1, 'O verbo "fazer" indicando tempo é impessoal — fica no singular.'],
                    ['Qual é o plural de "cidadão"?', ['Cidadões', 'Cidadãos', 'Cidadães', 'Cidadans'], 1, '"Cidadão" tem plural irregular: "cidadãos" (como "irmãos").'],
                    ['Marque a frase sem erro de português:', ['Prefiro chocolate do que baunilha.', 'Prefiro chocolate a baunilha.', 'Prefiro mais chocolate.', 'Prefiro chocolate mais que baunilha.'], 1, 'Prefere-se sempre X a Y (não "do que").'],
                    ['Qual é o antônimo de "efêmero"?', ['Passageiro', 'Duradouro', 'Rápido', 'Fugaz'], 1, 'Efêmero = passageiro, breve. Antônimo: duradouro.'],
                    ['Onde a vírgula está correta?', ['Comprei arroz, feijão, e macarrão.', 'Comprei arroz, feijão e macarrão.', 'Comprei arroz feijão e macarrão.', 'Comprei, arroz, feijão e macarrão.'], 1, 'Antes do "e" no fim da enumeração não se usa vírgula.'],
                    ['A palavra "há" em "Há dois anos" significa:', ['Existe', 'Faz (tempo passado)', 'Havia', 'Terá'], 1, '"Há" com H indica tempo passado (equivale a "faz"). "A" seria tempo futuro.'],
                    ['Qual está correto?', ['Se eu ver ele, aviso.', 'Se eu vir ele, aviso.', 'Se eu vê-lo, aviso.', 'Se eu ver-lo, aviso.'], 1, 'Verbo "ver" no futuro do subjuntivo: "vir" (não confundir com o verbo "vir").'],
                    ['Assinale o uso adequado de "mal" ou "mau":', ['Ele fez mau para a saúde.', 'Ele fez mal para a saúde.', 'Ele fez mal a saúde.', 'Ele fez mau à saúde.'], 1, '"Mal" (advérbio, oposto de bem) ≠ "Mau" (adjetivo, oposto de bom).'],
                    ['O plural de "guarda-chuva" é:', ['Guarda-chuva', 'Guardas-chuva', 'Guarda-chuvas', 'Guardas-chuvas'], 2, 'Só o substantivo (chuva) vai para o plural: "guarda-chuvas".'],
                    ['A frase "Fui ao médico porque estava doente" é:', ['Simples', 'Composta por coordenação', 'Composta por subordinação', 'Sem verbo'], 2, '"Porque" introduz oração subordinada adverbial causal.'],
                    ['Qual acentuação está correta?', ['Ídeia', 'Idéia', 'Ideia', 'Ideía'], 2, 'Após o Acordo Ortográfico, "ideia" não leva mais acento.'],
                    ['O correto para uso em e-mail formal é:', ['E aí, tudo bem?', 'Prezado(a), boa tarde.', 'Oi galera!', 'Fala fera'], 1, 'Formalidade profissional pede saudação neutra e educada.'],
                    ['Assinale a alternativa com concordância correta:', ['Menos pessoas vieram.', 'Menas pessoas vieram.', 'Meno pessoas vieram.', 'Menos pessoa vieram.'], 0, '"Menos" é invariável — nunca se flexiona em feminino ("menas" é incorreto).'],
                    ['Qual é o significado de "ambíguo"?', ['Claro', 'Que tem duplo sentido', 'Rápido', 'Longo'], 1, 'Ambíguo = com mais de uma interpretação possível.'],
                    ['Marque a frase correta:', ['Espero que ele vem.', 'Espero que ele venha.', 'Espero que ele vier.', 'Espero que ele vim.'], 1, 'O verbo "esperar" pede subjuntivo: "que venha".'],
                    ['A abreviação de "vossa senhoria" é:', ['V.S.', 'V.Sa.', 'V/S', 'VS'], 1, 'V.Sa. é a forma padrão. V.Exa. é para "Vossa Excelência".'],
                    ['Complete: "Se você _____ tempo, faça o exercício."', ['ter', 'tiver', 'terá', 'tem'], 1, 'Após "se" no futuro do subjuntivo, usa-se "tiver".'],
                ],
            ],

            /* ============================================================
             |  4) INGLÊS BÁSICO
             |============================================================ */
            [
                'title' => 'Inglês Básico',
                'slug'  => 'ingles-basico',
                'category' => 'Idiomas',
                'short_description' => 'Vocabulário e gramática essenciais para o dia a dia profissional.',
                'description' => 'Nível A1-A2 de inglês: greetings, present/past simple, preposições, vocabulário básico de trabalho.',
                'difficulty' => 'basic',
                'icon' => 'academic', 'color' => 'blue',
                'questions' => [
                    ['Complete: "She ___ a teacher."', ['am', 'is', 'are', 'be'], 1, 'She/He/It → is. I → am. You/We/They → are.'],
                    ['Traduza: "I have a meeting tomorrow."', ['Eu tinha uma reunião ontem.', 'Eu tenho uma reunião amanhã.', 'Eu terei uma reunião hoje.', 'Eu tive uma reunião.'], 1, '"Have" (presente) + "tomorrow" = amanhã.'],
                    ['What is the past of "go"?', ['goed', 'went', 'gone', 'going'], 1, 'Verbo irregular: go → went → gone.'],
                    ['Complete: "There ___ many people here."', ['is', 'are', 'am', 'be'], 1, '"People" é plural → "are". "Person" seria "is".'],
                    ['"Nice to meet you" means:', ['Boa noite', 'Prazer em conhecê-lo', 'Até logo', 'Como vai?'], 1, 'Frase padrão ao ser apresentado a alguém.'],
                    ['Qual é a tradução de "email attachment"?', ['Cabeçalho de e-mail', 'Anexo de e-mail', 'Assinatura', 'Assunto'], 1, '"Attachment" = anexo. "Subject" = assunto.'],
                    ['Complete: "I ___ speak Portuguese."', ['can', 'must', 'should', 'have'], 0, '"Can" indica habilidade/capacidade.'],
                    ['"How much does it cost?" pergunta:', ['O que é isso?', 'Quanto custa?', 'Como funciona?', 'Onde está?'], 1, '"How much" = quanto (para dinheiro/quantidade não contável).'],
                    ['Qual palavra é o plural correto de "child"?', ['childs', 'childrens', 'children', 'childs\'s'], 2, '"Child" tem plural irregular: "children".'],
                    ['Traduza: "Could you help me, please?"', ['Você me ajudou?', 'Você poderia me ajudar, por favor?', 'Você vai me ajudar?', 'Ajude-me!'], 1, '"Could" indica pedido educado.'],
                    ['Complete: "The meeting is ___ Monday."', ['in', 'at', 'on', 'to'], 2, 'Dias da semana → "on". Meses/anos → "in". Horas → "at".'],
                    ['Qual é o oposto de "expensive"?', ['cheap', 'big', 'good', 'fast'], 0, '"Expensive" = caro. "Cheap" = barato.'],
                    ['"I don\'t understand" significa:', ['Eu entendo', 'Eu não entendo', 'Eu concordo', 'Eu explico'], 1, 'Do + not (don\'t) → negativa do verbo "understand".'],
                    ['Complete: "___ is your name?"', ['Who', 'What', 'When', 'Where'], 1, '"What is your name?" = Qual é o seu nome?'],
                    ['A saudação padrão de e-mail formal é:', ['Hey!', 'Dear Sir/Madam,', 'What\'s up?', 'Hi guy'], 1, '"Dear" é a fórmula tradicional em e-mails de negócios.'],
                    ['Qual verbo está no passado?', ['work', 'works', 'worked', 'working'], 2, '"Worked" (regular verb -ed) é o simple past de "work".'],
                    ['Traduza: "next week"', ['semana passada', 'semana que vem', 'esta semana', 'todo dia'], 1, '"Next" = próximo(a). "Last week" = semana passada.'],
                    ['Complete: "I would like ___ coffee."', ['a', 'an', 'the', 'some'], 0, 'Coffee (contável em xícara) — "a coffee" (uma xícara).'],
                    ['"Deadline" em português é:', ['Prazo final', 'Data de início', 'Meio-termo', 'Longa data'], 0, '"Deadline" = prazo final. Muito usado no ambiente corporativo.'],
                    ['Complete: "She has ___ experience."', ['many', 'much', 'a lot', 'few'], 1, '"Experience" (não contável) → "much" em negativas/interrogativas. Em afirmativas seria "a lot of".'],
                ],
            ],

            /* ============================================================
             |  5) MATEMÁTICA BÁSICA
             |============================================================ */
            [
                'title' => 'Matemática Básica',
                'slug'  => 'matematica-basica',
                'category' => 'Raciocínio',
                'short_description' => 'Porcentagens, regra de três, frações e operações do dia a dia.',
                'description' => 'Cálculos essenciais para o mercado de trabalho: descontos, juros simples, proporções e conversões.',
                'difficulty' => 'basic',
                'icon' => 'sparkles', 'color' => 'accent',
                'questions' => [
                    ['Quanto é 20% de 150?', ['15', '30', '20', '25'], 1, '20% = 20/100. 0,20 × 150 = 30.'],
                    ['Se uma camisa custa R$80 e tem 25% de desconto, o preço final é:', ['R$55', 'R$60', 'R$65', 'R$70'], 1, '25% de 80 = 20. Desconto: 80 - 20 = 60.'],
                    ['Quanto é 3/4 em decimal?', ['0,25', '0,50', '0,75', '1,25'], 2, '3 ÷ 4 = 0,75.'],
                    ['Regra de três: se 3 kg custa R$15, quanto custa 5 kg?', ['R$20', 'R$25', 'R$30', 'R$35'], 1, '15/3 = 5 por kg. 5 × 5 = 25.'],
                    ['Qual é 10% de 250?', ['15', '20', '25', '30'], 2, '10% = dividir por 10 → 25.'],
                    ['Se 5 operários fazem uma obra em 10 dias, quantos dias para 10 operários?', ['5', '10', '15', '20'], 0, 'Grandezas inversamente proporcionais: 10/2 = 5.'],
                    ['Quanto é 2 + 3 × 4?', ['20', '14', '11', '10'], 1, 'Multiplicação antes: 3×4=12, depois 2+12=14.'],
                    ['Um produto de R$200 recebe aumento de 15%. Novo preço:', ['R$215', 'R$220', 'R$230', 'R$240'], 2, '15% de 200 = 30. 200 + 30 = 230.'],
                    ['Qual fração equivale a 50%?', ['1/2', '1/4', '2/3', '3/4'], 0, '50% = 50/100 = 1/2.'],
                    ['Se hoje é quarta-feira, que dia será daqui a 100 dias?', ['Domingo', 'Segunda', 'Sábado', 'Sexta'], 3, '100 ÷ 7 = 14 semanas e sobra 2 dias. Quarta + 2 = sexta.'],
                    ['Um investimento de R$1000 rende 5% ao mês. Ao final do 1º mês:', ['R$1005', 'R$1050', 'R$1500', 'R$1050 mais impostos'], 1, '5% de 1000 = 50. Total: 1050.'],
                    ['Quanto é 1/2 + 1/4?', ['1/6', '2/6', '3/4', '1/8'], 2, 'MMC de 2 e 4 é 4: 2/4 + 1/4 = 3/4.'],
                    ['O dobro de 15% é:', ['20%', '25%', '30%', '35%'], 2, '2 × 15% = 30%.'],
                    ['Se A=10 e B=5, quanto é (A+B)×2?', ['25', '30', '20', '35'], 1, '(10+5) × 2 = 30.'],
                    ['Quanto é 7 ao quadrado?', ['14', '21', '49', '77'], 2, '7² = 7 × 7 = 49.'],
                    ['Uma sala tem 3m × 4m. Qual a área?', ['7m²', '10m²', '12m²', '14m²'], 2, 'Área = base × altura = 3 × 4 = 12m².'],
                    ['Se 40 é 20% de X, então X é:', ['200', '160', '180', '220'], 0, '40 = 0,20X → X = 40/0,20 = 200.'],
                    ['Média de 6, 8 e 10:', ['6', '7', '8', '9'], 2, 'Soma: 24. Divide por 3: 8.'],
                    ['A raiz quadrada de 81 é:', ['7', '8', '9', '10'], 2, '9 × 9 = 81.'],
                    ['Se um carro anda 60 km/h, em 2h30min percorre:', ['120 km', '150 km', '180 km', '210 km'], 1, 'Distância = velocidade × tempo. 60 × 2,5 = 150 km.'],
                ],
            ],
        ];
    }
}
