<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\LeadsModel\Contact;
use App\Model\LeadsModel\Action;

class UpdateEmailContacts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:UpdateEmailContacts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update database ';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()

    {   
        //update contacts emails
        self::updateContactsEmails();
        //Update Action user_id where empty 
      //  self::updateActionsUserId();

        
    }

     /**
    * Update contacts with specified data
    *
    */
    private function updateContactsEmails(){
        // data to update
        $data = array(
            '8327980720'=>'charlespeterderi@comcast.net',
            '3056623739'=>'lcraaustin@hotmail.com',
            '9544318336'=>'vhampe@comcast.net',
            '5083946587'=>' joebarb06@comcast.net',
            '4078860634'=>'scarlet464@aol.com',
            '8632959248'=>'db.wrhoa@gmail.com',
            '2392061594'=>'newenglandgolf@comcast.net',
            '5614876929'=>'hfeingold@comcast.net',
            '9413499125'=>'kgewilson@gmail.com',
            '3862469563'=>'diatort2@gmail.com',
            '8634530448'=>'dddouble19@gmail.com',
            '7726317910'=>'c.sanders3604@comcast.net',
            '3057927242'=>'pointeastthree@gmail.com',
            '2404080108'=>'william.j.gureck@gmail.com',
            '2392901845'=>'carolek@coconet.com',
            '6317361705'=>'djmelucci@outlook.com',
            '7277847509'=>'curtistomlin87@gmail.com',
            '8632019110'=>'jamesvanderveer@comcast.net',
            '9545923599'=>'mvmc@bellsouth.net',
            '9044719184'=>'jhspeedbird@aol.com',
            '7863484936'=>'alonzohudson@yahoo.com',
            '3156357923'=>'chriscane@yahoo.com',
            '3156990281'=>'tfdfmoro@hotmail.com',
            '3052741068'=>'tomkatz66@hotmail.com',
            '6317342445'=>'jack.hochstrasser960@gmail.com',
            '8606910234'=>'whenderson2234@sbcglobal.net',
            '2393892532'=>'tfdfmoro@hotmail.com',
            '3867618005'=>'gboycej@earthlink.net',
            '3052924828'=>'kwrun4fun@yahoo.com',
            '2395723229'=>'djt3229@comcast.net',
            '7279370855'=>'bumpmck@gmail.com',
            '5618831298'=>'norman.defusco@comcast.net',
            '9545320892'=>'jfcat2@live.com',
            '2392259260'=>'dickdundee@comcast.net',
            '2395937738'=>'paulfmurphy24@aol.com',
            '8506500743'=>'monteschoenfeld@gmail.com',
            '9544358866'=>'afernandezcpa@gmail.com',
            '2396420733'=>'sthayer701@yahoo.com',
            '9417788686'=>'offshore.ami@gmail.com',
            '7277254138'=>'fixdianne@gmail.com',
            '7723342712'=>'fmm3@aol.com',
            '2395989523'=>'hg@filterequipment.com ',
            '3522629523'=>'gary.islandhouse@gmail.com',
            '5617483321'=>'cliffjet@aol.com',
            '4143505855'=>'b_goetz@yahoo.com',
            '5618146333'=>'merrillgottlieb@gmail.com',
            '7275883307'=>'rastailey@sbcglobal.net',
            '2395142864'=>'dmsveen@aol.com',
            '3052679573'=>'dania.rouco@att.net',
            '5853151200'=>'eddiep@rochester.rr.com',
            '4076227060'=>'ludin@lefcpa.com',
            '3523825558'=>'bwarren109@tampabay.rr.com',
            '7578759799'=>'shantiniketanone@gmail.com',
            '9549707109'=>'cotherman@att.net',
            '6302080703'=>'alleegater@aol.com',
            '7276923782'=>'codogger@aol.com',
            '2394316212'=>'jzamarro@aol.com',
            '3212680020'=>'sommerspt@att.net',
            '2395141417'=>'rethompson@snet.net',
            '6128602331'=>'jlhoffert@hotmail.com',
            '3523788223'=>'mike.islandhouse@gmail.com',
            '7273974860'=>'dpe55@msn.com',
            '7724893767'=>' jackieross@att.net ',
            '9414123743'=>'bill@feigley.com',
            '5614454905'=>'westbocapaul@aol.com',
            '5616403373'=>' faheyjjp@aol.com',
            '3052459102'=>'markareyes@gmail.com',
            '9414123743'=>'bill@feigley.com',
            '5616868942'=>'jerrysled65@gmail.com',
            '2395910588'=>'falesniak622@gmail.com',
            '5617130112'=>'belardo132@comcast.net',
            '5616161981'=>'suzzerie@yahoo.com',
            '7279343940'=>'jhawes2@tampabay.rr.com',
            '4076471895'=>'wweir111@gmail.com',
            '8636448151'=>'susanbain3000@gmail.com',
            '9414800935'=>'gandplindsay@yahoo.com',
            '7153255263'=>'dukeandbev@hotmail.com',
            '2544734299'=>'poneill625@gmail.com',
            '9544215468'=>'eerenaudvt@gmail.com',
            '7722297791'=>'9550parrothead@gmail.com',
            '3052459102'=>'markareyes@gmail.com',
            '3053434941'=>'nfig29@bellsouth.net',
            '9413792093'=>'dianelea@comcast.net',
            '2396316296'=>'jmontopoli@aol.com',
            '3058917722'=>'jpjagq@aol.com',
            '2394813790'=>'drm1335@aol.com',
            '3216132056'=>'ellenparda@yahoo.com',
            '9147378429'=>'clsadlon@gmail.com',
            '6094332077'=>' gelle62@aol.com',
            '3056929180'=>'steven_weisberg@bellsouth.net',
            '3055971295'=>'jweinber1055@gmail.com',
            '3055971295'=>' jweinber1055@gmail.com',
            '5616385585'=>'sgb1128@bellsouth.net',
            '5614339490'=>'pschack@bellsouth.net',
            '4078088387'=>'bw.sk@hotmail.com ',
            '7279450783'=>'lincavpav@gmail.com',
            '3864459353'=>'dfandefsowers@aol.com',
            '9544394738'=>'lockett7@bellsouth.net',
            '7867135007'=>'n18608@gmail.com',
            '3059699202'=>'jforte47@yahoo.com',
            '7279404468'=>'cjohn4444@aol.com',
            '6177336502'=>'wrightnow@netscape.com',
            '7275179166'=>'gkward@tampabay.rr.com',
            '7708629515'=>'rwfgolf@comcast.net',
            '2397681452'=>'eldoreeves44@gmail.com',
            '5617312557'=>'palladiumhoa@comcast.net',
            '7722863881'=>'cmbrvj@yahoo.com',
            '4073543144'=>'ecw7007@aol.com',
            '5857381349'=>'rcluff5866@aol.com',
            '5613923661'=>'pinesboca@yahoo.com',
            '9546551251'=>'marta_la@hotmail.com',
            '5612556232'=>'cristina123ic@hotmail.com',
            '6178946608'=>'lt3952@gmail.com',
            '3862261087'=>'campanale@aol.com',
            '3059999284'=>'riettiep@bellsouth.net',
            '3059999284'=>'riettiep@bellsouth.net',
            '9545838935'=>'johnlnelson56@gmail.com',
            '5613944995'=>'par@peterroselaw.com',
            '2032734010'=>'angela511@aol.com',
            '2397681452'=>'eldoreeves44@gmail.com',
            '9528316166'=>'mielkerl33@gmail.com',
            '2399859747'=>'janedilena12@comcast.net',
            '4078590096'=>'bardanforever@bellsouth.net',
            '3055598441'=>'dwkatzberg@yahoo.com',
            '2397767529'=>'luigicampoli@comcast.net',
            '3219391659'=>'vemotley@aol.com',
            '9043632360'=>'desmgt@aol.com',
            '5619885584'=>'george.m.shapiro@gmail.com',
            '3058543045'=>'mhadler@comcast.net',
            '2392062190'=>'larryfsmith9325@comcast.net',
            '9418969043'=>' phyljimlbk@gmail.com',
            '3052459102'=>'markareyes@gmail.com',
            '5616250514'=>'mfleming@comcast.net',
            '9418969043'=>' phyljimlbk@gmail.com',
            '9417532071'=>'bikbek1931@yahoo.com',
            '3219391659'=>'vemotley@aol.com',
            '4076201264'=>'ileanacalonge@hotmail.com',
            '3105029969'=>'gdb333@gmail.com',
            '2394545750'=>'bigm1ltlj2@gmail.com',
            '2397937070'=>'ejay198@aol.com',
            '9542701916'=>'cdavid2@comcast.net ',
            '8139619717'=>'vpcvchase@gmail.com',
            '9419521793'=>'raymarfla@aol.com',
            '9542553216'=>'dpadnis@gmail.com',
            '9545541569'=>'ptca6701@att.net',
            '7653424956'=>'jmprett2225@msn.com',
            '4013640642'=>'davidhowe75@yahoo.com',
            '6109371281'=>'leftyontee@yahoo.com',
            '5617507878'=>'rick@jrdiamondsupply.com',
            '8457754458'=>'candito1@hvc.rr.com',
            '9544365888'=>'ehccondo@comcast.net',
            '7273680322'=>'restran2@gmail.com',
            '7274120855'=>'gail.moore@fsresidential.com',
            '9415757183'=>'acm.notices@comcast.net',
            '7274413789'=>'rjwitt2@tampabay.rr.com',
            '9413834415'=>'debnyman1@verizon.net',
            '9417272426'=>' lynnvolleyball@outlook.com ',
            '7278687474'=>'davemeachum@yahoo.com ',
            '5616240175'=>'tgobrien@comcast.net',
            '7865149315'=>'rayonhoward@yahoo.com',
            '7273760265'=>'vcsylvester58@yahoo.com',
            '8139202714'=>'claudettesec@tampabay.rr.com',
            '9415387335'=>'franjones@tampabay.rr.com',
            '4072215713'=>'cascadesoflauderhill@gmail.com',
            '9892459823'=>'skarora@svsu.edu',
            '5614340306'=>'tabbw@comcast.net ',
            '5614527428'=>'afnemeth@gmail.com',
            '2394982376'=>'scott.str@gmail.com',
            '6128674559'=>'mvnltd@yahoo.com',
            '5628331388'=>'ashbyccondo@gmail.com',
            '6308865438'=>' southgarden750@gmail.com',
            '9043334242'=>'atlanticeast@att.net',
            '9544201070'=>'eli_okun@yahoo.com',
            '9544868695'=>'environtowers2@gmail.com',
            '5163592902'=>'2luckeys@optonline.net ',
            '7723324703'=>'yourrealtor.karen@gmail.com',
            '7346346016'=>'gpvonderheide@gmail.com',
            '5617189724'=>'jamesawitmer@comcast.net',
            '3219726665'=>'nef.public@gmail.com',
            '9413885145'=>'patnicosia@yahoo.com',
            '5616412099'=>'revtaw@aol.com',
            '9548151493'=>'robertciv@gmail.com',
            '9548027079'=>'neilellisk@yahoo.com',
            '3057263805'=>'promenadecondo@comcast.net',
            '5614613558'=>'bbillthomas@aol.com',
            '9144740207'=>'sparkle71111@yahoo.com ',
            '6317422839'=>'edepa@aol.com',
            '9543269010'=>'jrichman929@gmail.com',
            '6824652264'=>'greeves.bs2@gmail.com',
            '5612908777'=>'dlblanton1@yahoo.com',
            '7274801515'=>'mc1.pres2020@gmail.com',
            '2035612403'=>'kenperren@comcast.net',
            '9542141692'=>'georgepyrpiris@gmail.com',
            '6468086633'=>'2002balharborvp@gmail.com',
            '9417227045'=>'james_sullivan_1999@yahoo.com',
            '7729327747'=>'gtrites819@gmail.com',
            '7276678868'=>'bevbob92@yahoo.com',
            '6513015529'=>'jnjhummerlv@yahoo.com',
            '5162097562'=>'eblack0327@gmail.com',
            '9548124910'=>'janetsz@aol.com',
            '5614455105'=>'scstettner@gmail.com',
            '9417136033'=>'xfarr5@aol.com',
            '8139515700'=>'cdisler55@yahoo.com ',
            '5614526336'=>'robt.barnett@gmail.com',
            '4432801151'=>'cmanewman@bellsouth.net',
            '2394309966'=>'afp@hungerinus.org',
            '3138064747'=>'pkarpen26019mi@comcast.net',
            '2603071528'=>'mdhoopengardner@yahoo.com',
            '6303372636'=>'dmsveen@aol.com',
            '5612714324'=>'boctlh@yahoo.com',
            '5618660428'=>'terrace800@comcast.net',
            '7542146542'=>'omarramadan@comcast.net',
            '9417946294'=>'soaringmkm@aol.com',
            '8132208630'=>'jackhenard10@gmail.com',
            '8122434968'=>'marthavietti@aol.com',
            '5612467664'=>'lodlt33484@gmail.com',
            '5616370710'=>'sueloeser@gmail.com',
            '9413515327'=>'joypende@gmail.com',
            '9548501444'=>'c.nahmiach@gmail.com',
            '5616387660'=>'monkey7212@att.net',
            '5614655251'=>'golfdemoss@aol.com',
            '7547035995'=>'hemispherespresident@gmail.com',
            '3864783747'=>'mkelifritz@aol.com',
            '3125933002'=>'scperl@comcast.net',
            '2396825018'=>'zbubbaz@comcast.net',
            '5613134471'=>'pbhtitle@live.com',
            '9204601245'=>'cutlass6872@att.net',
            '2033629079'=>'dennisling1@gmail.com',
            '5618829285'=>'ffollari@follarigroup.com',
            '7275603302'=>'tlcmoney@hotmail.com',
            '2397765480'=>'jqueck@yahoo.com',
            '5617400363'=>'carlsloan@aol.com',
            '5617337683'=>'jeanie@elitemg.net ',
            '3059350525'=>'laurier17800@att.net',
            '9549413578'=>'hsaff@bellsouth.net',
            '5617370012'=>'margotkb3@gmail.com',
            '3059404995'=>'dddelong22@comcast.net',
            '9549175930'=>'jaa4148@aol.com ',
            '9546472761'=>'joemymy@comcast.net',
            '5612186402'=>'cool2read@aol.com',
            '3057767615'=>'pattygoldsmith@aol.com',
            '4073760241'=>'rickrpinson@hotmail.com',
            '5613387083'=>'tlynmazza@gmail.com',
            '9042618562'=>'rvhen607@aol.com',
            '9419217624'=>'lonima085@gmail.com',
            '3478402552'=>'baccalla13@aol.com',
            '7275775350'=>'al_ferraro@hotmail.com',
            '5024351439'=>'ronald.e.harris@gmail.com',
            '9737223942'=>'ahaller@att.net',
            '4075952369'=>'dsantossiennaplace@gmail.com',
            '3056516002'=>'romontgreen@yahoo.com',
            '3174094424'=>'bar1425@yahoo.com',
            '3052827634'=>'lojalvo1006@gmail.com',
            '7742761587'=>'amc1966fountain@gmail.com',
            '9543841667'=>'mitchellf@comcast.net',
            '7862629009'=>'jmcamino@comcast.net',
            '2392503092'=>'presidentmartysv@gmail.com',
            '5615859421'=>'lornork3@optonline.net ',
            '3059756336'=>'countyline18@hotmail.com',
            '3528122200'=>'chester@ocalalawfirm.com',
            '9546636744'=>'woodscapedona@gmail.com',
            '3055886626'=>'rosie0057@att.net',
            '5617353170'=>'conorabu@gmail.com',
            '9544243949'=>'lglplan1@gmail.com',
            '8433381116'=>'cinnamonfrick@gmail.com',
            '5613154588'=>'bosco101@bellsouth.net',
            '5169838184'=>'jdtaco@aol.com',
            '7865432152'=>'garysec@wt500.org',
            '2393572284'=>'dncearly@comcast.net',
            '4079607090'=>'tiffanylytle@gmail.com',
            '8133352304'=>'winstongatewayboard@gmail.com',
            '7275857092'=>'bjcoogler@yahoo.com',
            '3057764994'=>'irene361@gmail.com',
            '6315783801'=>'craigjen9@comcast.net',
            '9543041721'=>'jenniesailboatpointe@gmail.com',
            '7272514680'=>'sgrasso4680@gmail.com',
            '9546655113'=>'darnel99@hotmail.com',
            '9545320137'=>'sidbick@yahoo.com',
            '2157385468'=>'jmmorgan@yahoo.com',
            '7273738627'=>'tisunny122@yahoo.com',
        );
        //log success error data
        $log['success']=collect();
        $log['error']=collect();
        
        foreach ($data as $phone=>$email){
            //get contact with phone 
            $contact = Contact::where('c_phone',$phone)->first(); 
          // if is contact with phone and email is empty, update email
            if($contact && empty($contact->c_email)){
                $contact->update(array('c_email'=>$email));//update contact email
                $log['success']->push($phone.' - '.$email);
            }else{
             
                $log['error']->push($phone.' - '.$email);
            }
        }

        print_r($log);
    }

     /**
    * Update Action user_id where empty 
    *
    */
    private function updateActionsUserId(){
        $actions = Action::all();
        foreach($actions as $action){
            if($action && empty($action->user_id)){
                $action->update(array('user_id'=>1));
            }
           
        }
       
    }



}
