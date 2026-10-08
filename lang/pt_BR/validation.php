<?php

declare(strict_types=1);

return [
    'required' => 'O campo :attribute é obrigatório.',
    'string' => 'O campo :attribute deve ser um texto.',
    'integer' => 'O campo :attribute deve ser um número inteiro.',
    'numeric' => 'O campo :attribute deve ser um número.',
    'boolean' => 'O campo :attribute deve ser verdadeiro ou falso.',
    'array' => 'O campo :attribute deve ser uma lista.',
    'enum' => 'A opção selecionada para :attribute é inválida.',
    'exists' => 'A opção selecionada para :attribute é inválida.',
    'unique' => 'Este valor de :attribute já está em uso.',
    'email' => 'O campo :attribute deve conter um e-mail válido.',
    'date' => 'O campo :attribute deve conter uma data válida.',
    'url' => 'O campo :attribute deve conter uma URL válida.',
    'regex' => 'O formato de :attribute é inválido.',
    'image' => 'O campo :attribute deve conter uma imagem.',
    'file' => 'O campo :attribute deve conter um arquivo.',
    'uploaded' => 'Não foi possível enviar o arquivo de :attribute.',
    'mimes' => 'O arquivo de :attribute deve ser do tipo: :values.',
    'mimetypes' => 'O arquivo de :attribute deve ser do tipo: :values.',
    'max' => [
        'string' => 'O campo :attribute deve ter no máximo :max caracteres.',
        'numeric' => 'O campo :attribute deve ser no máximo :max.',
        'file' => 'O arquivo de :attribute deve ter no máximo :max KB.',
        'array' => 'O campo :attribute deve ter no máximo :max itens.',
    ],
    'min' => [
        'string' => 'O campo :attribute deve ter pelo menos :min caracteres.',
        'numeric' => 'O campo :attribute deve ser no mínimo :min.',
        'file' => 'O arquivo de :attribute deve ter pelo menos :min KB.',
        'array' => 'O campo :attribute deve ter pelo menos :min itens.',
    ],
    'between' => [
        'numeric' => 'O campo :attribute deve estar entre :min e :max.',
    ],
    'attributes' => [
        'name' => 'nome', 'slug' => 'slug', 'sku' => 'SKU', 'category_id' => 'categoria',
        'description' => 'descrição', 'short_description' => 'descrição curta',
        'cost_price' => 'preço de custo', 'price' => 'preço de venda', 'promotional_price' => 'preço promocional',
        'availability' => 'disponibilidade', 'stock' => 'estoque', 'youtube_url' => 'vídeo do YouTube',
        'weight' => 'peso', 'height' => 'altura', 'width' => 'largura', 'length' => 'comprimento',
        'sort_order' => 'ordem', 'new_images' => 'fotos', 'new_images.*' => 'foto',
    ],
];
