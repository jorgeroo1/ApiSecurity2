<?php
namespace App\Tests\Functional;

use App\Factory\DragonTreasureFactory;
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
        DragonTreasureFactory::createMany(5, [
            'isPublished' => true,
        ]);
        DragonTreasureFactory::createOne([
            'isPublished' => false,
        ]);
        //no necesitas un codigo de estado porque estás comprobando el contenido de manera directa
        $json = $this->browser()
            ->get('/api/treasures')
            //ya comprueba si el json es valido
            ->assertJson()
            ->assertJsonMatches('"hydra:totalItems"', 5)
            ->assertJsonMatches('length("hydra:member")', 5)
            ->json()
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
            ]))
            //assertStatus es para establecer el codigo de estado que esperamos aqui como hay datos correctos esperamos un 201
            ->assertStatus(201)
            ->assertJsonMatches('name', 'A shiny thing')
        ;
    }
//    public function testPostToCreateTreasureWithApiKey(): void
//    {
//        //en DragonTreasure hemos dicho que solo se podrá crear (POST) si el usuario
//        //tiene el rol ROLE_TREASURE_CREATE que si nos vamos a la entidad ApiToken
//        //coincide con SCOPE_TREASURE_CREATE por eso con el SCOPE_TREASURE_EDIT te da un error 403
//        //y la prueba falla porque hemos dicho que esperamos un 422
//        $token = ApiTokenFactory::createOne([
//            'scopes' => [ApiToken::SCOPE_TREASURE_EDIT]
//        ]);
//        $this->browser()
//            ->post('/api/treasures', [
//                //no hay datos a insertar en esta peticion por eso esperamos un 422, error de validacion
//                'json' => [],
//                'headers' => [
//                    'Authorization' => 'Bearer '.$token->getToken()
//                ]
//            ])
//            ->assertStatus(422)
//        ;
//    }
    public function testUpdatePatchTreasure()
    {
        $user = UserFactory::createOne();
        $treasure = DragonTreasureFactory::createOne(['owner' => $user]);
        $this->browser()
            ->actingAs($user)
            ->patch('/api/treasures/'.$treasure->getId(), [
            'json' => [
                'value' => 10,
            ],
        ])
        ->assertStatus(200)
        ->assertJsonMatches('value', 10);
        $user2 = UserFactory::createOne();
        $this->browser()
            ->actingAs($user2)
            ->patch('/api/treasures/'.$treasure->getId(), [
                'json' => [
                    'value' => 9345,
                    'owner' => 'api/users/'.$user2->getId(),
                ],
            ])
            ->assertStatus(403);
        $this->browser()
            //la principal diferencia es quien es el usuario original actingAs($user)
             //con la actual configuracion security: 'is_granted("ROLE_TREASURE_EDIT") and object.getOwner() == user',
            //esto me daria un 200 porque cambia el usuario cuando llega al array json esto es porque segurity de la entidad
             //DragonTreasure se ejecuta antes de llegar al json y por eso el codigo es 200 para prevenir eso hay que añadir
            //securityPostDenormalize
            ->actingAs($user)
            ->patch('/api/treasures/'.$treasure->getId(), [
                'json' => [
                    // change the owner to someone else
                    'owner' => '/api/users/'.$user2->getId(),
                ],
            ])
            ->assertStatus(422)
        ;
    }
    public function testAdminPatchTreasure(){
        //con el new creas una nueva instancia de User pero le das role de admin ademas de llamar defaults
        //con el createOne solo llamas a defaults
        $admin = UserFactory::new()->asAdmin()->create();
        $treasure = DragonTreasureFactory::createOne();
        $this->browser()
            //estamos actuando como admin y queremos modificar un registro
            ->actingAs($admin)
            ->patch('/api/treasures/'.$treasure->getId(), [
                'json' => [
                    'value' => 12222,
                ]
            ])
            ->assertStatus(200);
    }
    public function testOwnerCanSeeIsPublishedAndIsMineFields(): void
    {
        $user = UserFactory::createOne();
        $treasure = DragonTreasureFactory::createOne([
            'isPublished' => true,
            'owner' => $user,
        ]);

        $this->browser()
            ->actingAs($user)
            ->patch('/api/treasures/'.$treasure->getId(), [
                'json' => [
                    'value' => 12345,
                ],
            ])
            ->assertStatus(200)
            ->assertJsonMatches('value', 12345)
            ->assertJsonMatches('isPublished', true)
        ;
    }
    public function testTreasuresCannotBeStolen(): void
    {
        $user = UserFactory::createOne();
        $otherUser = UserFactory::createOne();
        //le decimos que el propietario de nuestro tesoro es otherUser pero luego estamos actuando como user
        $dragonTreasure = DragonTreasureFactory::createOne(['owner' => $otherUser]);
        $this->browser()
            ->actingAs($user)
            //como user (no propietario original) queremos cambiar los tesoros
                //pero gracias al propio validator que hemos creado esto no se permite y por eso
                //no nos falla la prueba (el codigo 422 está bien)
            ->patch('/api/users/' . $user->getId(), [
                'json' => [
                    'username' => 'changed',
                    'dragonTreasures' => [
                        '/api/treasures/' . $dragonTreasure->getId(),
                    ],
                ],
                'headers' => ['Content-Type' => 'application/merge-patch+json']
            ])
            ->assertStatus(422);
    }


}
