<?php

declare(strict_types=1);

namespace App\Filament\Resources\BlogPosts;

use App\Actions\Blog\DeleteBlogPostAction;
use App\Actions\Blog\RegisterBlogTermAction;
use App\Domain\Blog\Models\BlogPost;
use App\Domain\Blog\Models\BlogTerm;
use App\Filament\Resources\BlogPosts\Pages\CreateBlogPost;
use App\Filament\Resources\BlogPosts\Pages\EditBlogPost;
use App\Filament\Resources\BlogPosts\Pages\ListBlogPosts;
use App\Filament\Support\CatalogForm;
use App\Support\Catalog\ProductDescription;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Livewire\Component;

final class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;

    protected static ?string $modelLabel = 'postagem';

    protected static ?string $pluralModelLabel = 'Postagens do blog';

    protected static string|\UnitEnum|null $navigationGroup = 'Conteúdo';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(['default' => 1, 'lg' => 3])->components([
            Section::make('Escrever postagem')
                ->description('Editor visual no estilo de blog: títulos, parágrafos, listas, citações e links.')
                ->columns(1)->columnSpan(['default' => 1, 'lg' => 2])->schema([
                    TextInput::make('title')->label('Título da postagem')->required()->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (?string $state, \Filament\Schemas\Components\Utilities\Set $set, \Filament\Schemas\Components\Utilities\Get $get): void {
                            if (blank($get('slug'))) {
                                $set('slug', Str::slug($state ?? ''));
                            }
                        }),
                    TextInput::make('slug')->label('Endereço (slug)')->maxLength(255)
                        ->helperText('Gerado a partir do título; você pode personalizar. Alterações após a publicação mudam o link da postagem.')
                        ->prefix('/blog/'),
                    RichEditor::make('content')->label('Conteúdo')->columnSpanFull()
                        ->toolbarButtons([
                            ['h2', 'h3', 'h4', 'bold', 'italic', 'underline', 'strike'],
                            ['bulletList', 'orderedList', 'blockquote', 'link'],
                            ['undo', 'redo'],
                        ])
                        ->formatStateUsing(fn (mixed $state): string => ProductDescription::html($state))
                        ->dehydrateStateUsing(fn (mixed $state): string => ProductDescription::html($state))
                        ->helperText('Texto formatado com segurança. Para imagens, use a imagem de destaque. Não são permitidos scripts, embeds ou HTML arbitrário.'),
                    Textarea::make('excerpt')->label('Resumo / chamada')->rows(3)->maxLength(2000)
                        ->helperText('Exibido nos cards da home e da página do blog. Se vazio, será gerado a partir do conteúdo.'),
                ]),
            Section::make('Publicação')
                ->description('Controle quando a postagem estará visível no site.')
                ->columnSpan(['default' => 1, 'lg' => 1])->schema([
                    Select::make('status')->label('Situação')->options([
                        'draft' => 'Rascunho (não público)',
                        'published' => 'Publicado / agendado',
                    ])->required()->default('draft')->native(false),
                    DateTimePicker::make('published_at')->label('Publicar em')->seconds(false)
                        ->helperText('Vazio: publica ao salvar. Uma data futura agenda a postagem. Rascunhos permanecem ocultos.'),
                    FileUpload::make('cover_image')->label('Imagem de destaque')->image()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->disk('public')->directory('blog/covers')->visibility('public')->maxSize(5120)
                        ->preventFilePathTampering(allowFilePathUsing: fn (string $file, Component $livewire): bool => in_array($file, $livewire->pendingBlogPaths, true))
                        ->helperText('JPG, PNG ou WebP, até 5 MB. Imagem dos cards e do artigo.'),
                    TextInput::make('cover_alt')->label('Descrição da imagem (acessibilidade)')->maxLength(255),
                    Select::make('category')->label('Categoria editorial')
                        ->options(function (\Filament\Schemas\Components\Utilities\Get $get): array {
                            $options = BlogTerm::query()->categories()->orderBy('name')->pluck('name', 'name')->all();
                            $existing = $get('category');
                            if (is_string($existing) && $existing !== '' && ! array_key_exists($existing, $options)) {
                                $options[$existing] = $existing;
                            }

                            return $options;
                        })
                        ->searchable()->native(false)->placeholder('Selecione uma categoria')
                        ->createOptionForm([
                            TextInput::make('name')->label('Nome da nova categoria')->required()->maxLength(100),
                        ])
                        ->createOptionUsing(fn (array $data): string => app(RegisterBlogTermAction::class)
                            ->execute('category', (string) ($data['name'] ?? ''))->name)
                        ->helperText('Selecione uma categoria já cadastrada ou clique em Criar para adicionar uma nova.'),
                    TagsInput::make('tags')->label('Etiquetas')->placeholder('Adicionar etiqueta')
                        ->suggestions(fn (): array => BlogTerm::query()->tags()->orderBy('name')->limit(500)->pluck('name')->all())
                        ->helperText('Comece a digitar para reutilizar etiquetas existentes, ou adicione uma nova. Até 15 etiquetas.'),
                ]),
            Section::make('Otimização para buscadores (SEO)')
                ->description('Opcional. Sem estes campos, usamos o título e o resumo automaticamente.')
                ->columns(['default' => 1, 'lg' => 2])->columnSpanFull()->schema([
                    TextInput::make('seo_title')->label('Título SEO')->maxLength(255)->helperText('Recomendado: até 60 caracteres.'),
                    Textarea::make('seo_description')->label('Descrição SEO')->maxLength(500)->rows(3)
                        ->helperText('Recomendado: até 160 caracteres.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            ImageColumn::make('cover_image')->label('Capa')->disk('public')->visibility('public')->square(),
            TextColumn::make('title')->label('Título')->searchable()->sortable()->description(fn (BlogPost $record): string => '/blog/'.$record->slug),
            TextColumn::make('category')->label('Categoria')->placeholder('Sem categoria'),
            TextColumn::make('status')->label('Publicação')->badge()->formatStateUsing(function (string $state, BlogPost $record): string {
                if ($state === 'draft') {
                    return 'Rascunho';
                }

                return $record->published_at?->isFuture() ? 'Agendado' : 'Publicado';
            })->color(fn (string $state): string => $state === 'draft' ? 'gray' : 'success'),
            TextColumn::make('published_at')->label('Data de publicação')->dateTime('d/m/Y H:i')->sortable()->placeholder('Não publicada'),
            TextColumn::make('updated_at')->label('Atualização')->dateTime('d/m/Y H:i')->sortable(),
        ])->filters([
            SelectFilter::make('status')->label('Situação')->options(['draft' => 'Rascunho', 'published' => 'Publicado/agendado']),
        ])->defaultSort('updated_at', 'desc')->recordActions([
            EditAction::make()->iconButton()->tooltip('Editar'),
            CatalogForm::delete(fn (BlogPost $record) => app(DeleteBlogPostAction::class)->execute($record))
                ->iconButton()->tooltip('Excluir'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBlogPosts::route('/'),
            'create' => CreateBlogPost::route('/create'),
            'edit' => EditBlogPost::route('/{record}/edit'),
        ];
    }
}
