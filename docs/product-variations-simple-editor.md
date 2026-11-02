# Cadastro simplificado de variações (inspirado no fluxo da Shopee)

Na tela **Admin → Produtos → Criar / Editar → Variações do produto**:

1. Se o produto é vendido em uma única configuração, **não crie variações**.
2. Em **Características que o comprador pode escolher**, clique em **Adicionar característica** e informe um nome livre, como `Voltagem`, `Capacidade`, `Modelo`, `Material`, `Fragrância` ou `Tamanho`. Não há lista predefinida.
3. Em **Valores**, digite cada opção e pressione **Enter**. Para `Voltagem`: `110 V`, `220 V`. Para `Capacidade`: `3 L`, `5 L`.
4. Clique em **Gerar / atualizar combinações**. Por exemplo, 2 voltagens × 2 capacidades geram 4 linhas na tabela.
5. Cada linha mostra a combinação e permite digitar **Preço próprio (R$)**, **Estoque**, **SKU** e **Ativa**. Preço e estoque vazios herdam os dados gerais do produto.
6. **Aplicar preço / estoque a todos** preenche todas as linhas em uma operação. Se você aplicar um novo preço próprio a todas, eventuais promoções próprias anteriores são removidas para impedir promoções superiores ao preço novo.
7. No botão **Detalhes** de cada linha, altere **promoção e embalagem específica**. Deixe as 4 medidas em branco para herdar as medidas gerais.
8. Clique **Salvar** no produto. **Gerar combinações não salva por si só.**

**Editar produto existente:** todas as variações já cadastradas são carregadas; os tipos e valores são extraídos das combinações e os IDs, estoque, SKUs, preços próprios e ajustes são mantidos. Ao gerar novamente, os dados são preservados quando a combinação continua igual, mesmo que você mude a ordem dos tipos. Combinações retiradas do formulário são desativadas somente ao salvar; se houver reserva de estoque ligada àquela combinação, o sistema bloqueia a operação.

**Limites:** até 5 tipos e até 150 combinações para evitar formulários lentos. A validação de estoque reservado e das regras de preços é a mesma do backend original. Nenhuma migration, tabela ou regra de negócio foi alterada.

**Exemplos:**

- Equipamento: Voltagem (110 V, 220 V) e Potência (500 W, 750 W) → 4 combinações.
- Embalagem de alimento: Peso (250 g, 500 g, 1 kg) → 3 combinações.
- Ferramenta: Material (Aço carbono, Inox) e Comprimento (30 cm, 50 cm) → 4 combinações.
- Cosmético: Fragrância (Lavanda, Cítrico) e Volume (200 ml, 500 ml) → 4 combinações.
- Vestuário: Tamanho (P, M, G) e Cor (Preto, Branco) → 6 combinações.

**Importante:** uma característica de variação é uma **escolha no momento da compra**, não qualquer informação técnica do produto. Por exemplo, se todos os itens possuem a mesma voltagem, informe-a na descrição; se o comprador pode escolher entre 110 V e 220 V, cadastre `Voltagem` como característica e gere as combinações. Peso e dimensões para frete permanecem nos campos próprios.
