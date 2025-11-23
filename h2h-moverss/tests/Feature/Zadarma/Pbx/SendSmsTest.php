<?php
//
//namespace Tests\Feature\Zadarma\Pbx;
//
//use Aloha\Twilio\Twilio;
//use App\Http\Controllers\Zadarma\PBXController;
//use App\Models\Division;
//use Illuminate\Foundation\Testing\DatabaseTransactions;
//use Illuminate\Http\UploadedFile;
//use Illuminate\Support\Facades\Storage;
//use Tests\Builders\Divisions\DivisionBuilder;
//use Tests\Builders\Partners\PartnerBuilder;
//use Tests\Builders\Trucks\NoteBuilder;
//use Tests\Builders\Trucks\TruckBuilder;
//use Tests\Builders\Users\UserBuilder;
//use Tests\TestCase;
//
//class SendSmsTest extends TestCase
//{
//    use DatabaseTransactions;
//
//    protected DivisionBuilder $divisionBuilder;
//    protected UserBuilder $userBuilder;
//    protected TruckBuilder $truckBuilder;
//    protected NoteBuilder $noteBuilder;
//    protected PartnerBuilder $partnerBuilder;
//
//    protected array $data;
//
//    public function setUp(): void
//    {
//        $this->divisionBuilder = resolve(DivisionBuilder::class);
//        $this->userBuilder = resolve(UserBuilder::class);
//        $this->truckBuilder = resolve(TruckBuilder::class);
//        $this->noteBuilder = resolve(NoteBuilder::class);
//        $this->partnerBuilder = resolve(PartnerBuilder::class);
//
//        parent::setUp();
//
//        $this->data = [];
//    }
//
//
//    public function success_send_sms_with_img()
//    {
//        Storage::fake('public');
//
//        $sender = $this->createStub(PBXController::class);
////        $domain = $this->createStub(Domain::class);
////        $version = $this->createStub(Version::class);
//        $sender->method('sendSmsAsTwilio')->willReturn(['test' => 'test']);
//
//        /** @var $division Division */
//        $division = $this->divisionBuilder->id(1)->create();
//        $this->session(['division' => $division]);
//
//        $user = $this->loginUser();
//
//        $img = UploadedFile::fake()->image("test.jpg");
//
//        $data = [
//            'phone' => '65576576',
//            'text' => 'some msg',
//            'attachments' => [$img]
//        ];
//
//        $this->post(route('pbx.send-sms'), $data)
//            ->dump()
//            ->assertJson([
//                'success' => true,
//                'msg' => 'Truck changed',
//
//            ])
//        ;
//    }
//}
