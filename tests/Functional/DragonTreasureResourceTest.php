<?php
namespace App\Tests\Functional;

use App\Entity\ApiToken;
use App\Factory\ApiTokenFactory;
use App\Factory\UserFactory;
use Zenstruck\Browser\HttpOptions;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;

class DragonTreasureResourceTest extends ApiTestCase
{
    use HasBrowser;
    use ResetDatabase;
    public function testGetCollectionOfTreasures(): void
    {
        $this->browser()
            ->get('/api/treasures')
            ->assertJson()
            ->assertJsonMatches('"hydra:totalItems"', 0)
        ;
    }
    public function testPostToCreateTreasure(): void
    {
        $user = UserFactory::createOne();
        $this->browser()
            //actuar como el usuario que se acaba de crear
            ->actingAs($user)
            ->post('/api/treasures', [
                'json' => [],
            ])
            ->assertStatus(422)
            //el objeto segundo del post puede ser un array o un objeto
                //llamado HttpOptions
                //se hace una peticion post a esa url con los datos necesarios
            ->post('/api/treasures', HttpOptions::json([
                'name' => 'A shiny thing',
                'description' => 'It sparkles when I wave it in the air.',
                'value' => 1000,
                'coolFactor' => 5,
                'owner' => '/api/users/'.$user->getId(),
            ]))
            //assertStatus es para establecer el codigo de estado que esperamos aqui como hay datos correctos esperamos un 201
            ->assertStatus(201)
            ->assertJsonMatches('name', 'A shiny thing')
        ;
    }
    public function testPostToCreateTreasureWithApiKey(): void
    {
        //en DragonTreasure hemos dicho que solo se podrá crear (POST) si el usuario
        //tiene el rol ROLE_TREASURE_CREATE que si nos vamos a la entidad ApiToken
        //coincide con SCOPE_TREASURE_CREATE por eso con el SCOPE_TREASURE_EDIT te da un error 403
        //y la prueba falla porque hemos dicho que esperamos un 422
        $token = ApiTokenFactory::createOne([
            'scopes' => [ApiToken::SCOPE_TREASURE_EDIT]
        ]);
        $this->browser()
            ->post('/api/treasures', [
                //no hay datos a insertar en esta peticion por eso esperamos un 422, error de validacion
                'json' => [],
                'headers' => [
                    'Authorization' => 'Bearer '.$token->getToken()
                ]
            ])
            ->assertStatus(422)
        ;
    }


}
