<?php
 include("config.php");
 $conn = new mysqli($mysql_url, $mysql_username, $mysql_passwd);
 ?>
<vxml version="2.1">
    <var name="lang"/>
    <var name="region"/>

    <form>
        <block>
            <prompt>
                <audio src="<?php echo $siteurl; ?>/audio.php?table=simple&amp;name=intro&amp;language=en"/>
                <break time="0.5s"/>
                <audio src="<?php echo $siteurl; ?>/audio.php?table=simple&amp;name=intro&amp;language=dut"/>
            </prompt>
            <goto next="#set_language"/>
        </block>
    </form>


    <form id="set_language">
        <field name="lang_field">
            <prompt>
            <break time="1.0s"/>
            <!-- For English press 1 -->
            <audio src="<?php echo $siteurl; ?>/audio.php?table=simple&amp;name=lang_choose&amp;language=en"/>
            <break time="1.0s"/>
            <!-- Voor Nederlands toets 2 -->
            <audio src="<?php echo $siteurl; ?>/audio.php?table=simple&amp;name=lang_choose&amp;language=dut"/>
            </prompt>

            <grammar type="application/x-nuance-gsl">
            [ dtmf-1 dtmf-2 ]
            </grammar>

            <filled>
                <if cond="lang_field==1">
                    <assign name="lang" expr="'en'"/>
                <else/>
                    <assign name="lang" expr="'dut'"/>
                </if>
                <goto next="#set_region"/>
            </filled>
        </field>
    </form>

    <form id="set_region">
        <field name="region_field">
            <prompt>
            <?php
             $sql = "SELECT name FROM u230489196_makeitrain.region";
             $result_query = $conn->query($sql);
             $regions = array();
             $menu_i = 1;
             while ($row = $result_query->fetch_assoc()) {
                 $region = $row["name"];
                 array_push($regions, $region);
                 echo <<<REGION
                 <audio expr="'$siteurl/audio.php?table=simple&amp;name=for&amp;language=' + lang"/>
                 <audio expr="'$siteurl/audio.php?table=region&amp;name=$region&amp;language=' + lang"/>
                 <audio expr="'$siteurl/audio.php?table=set_region&amp;name=press&amp;language=' + lang"/>
                 <audio expr="'$siteurl/audio.php?table=numbers&amp;name=$menu_i&amp;language=' + lang"/>
                 <break time="1.0s"/>\n
                 REGION;
                 $menu_i += 1;
             }
             echo <<<CHANGE_LANGUAGE
             <audio expr="'$siteurl/audio.php?table=set_region&amp;name=change_lang&amp;language=' + lang"/>
             <audio expr="'$siteurl/audio.php?table=set_region&amp;name=press&amp;language=' + lang"/>
             <audio expr="'$siteurl/audio.php?table=numbers&amp;name=$menu_i&amp;language=' + lang"/>\n
             CHANGE_LANGUAGE;
             ?>
            </prompt>

            <grammar type="application/x-nuance-gsl">
            [ <?php
                for ($i = 0; $i < count($regions) + 1; $i++) {
                    $j = $i + 1;
                    echo "dtmf-$j ";
                }
               ?>]
            </grammar>

            <filled>
                <if cond="region_field==1">
                    <assign name="region" expr="'<?php echo $regions[0]; ?>'"/>
                <?php
                  for ($i = 1; $i < count($regions); $i++) {
                      $j = $i + 1;
                      $region = $regions[$i];
                      echo <<<SET_REGION_VAR
                      <elseif cond="region_field==$j"/>
                          <assign name="region" expr="'$region'"/>\n
                      SET_REGION_VAR;
                  }
                 ?>
                <else/>
                    <goto next="#set_language"/>
                </if>
                <goto next="#prediction_choose"/>
            </filled>
        </field>
    </form>

    <form id="prediction_choose">
        <field name="requested_pred_type">
            <prompt>
            <!-- For / Voor -->
            <audio expr="'<?php echo $siteurl; ?>/audio.php?table=set_prediction&amp;name=for&amp;language=' + lang"/>
            <break time="0.5s"/>
            <!-- The chosen region -->
            <audio expr="'<?php echo $siteurl; ?>/audio.php?table=region&amp;name=' + region + '&amp;language=' + lang"/>
            <break time="1.0s"/>

            <!-- Toets 1 voor een regenvoorspelling. / Press 1 for a rain prediction. -->
            <audio expr="'<?php echo $siteurl; ?>/audio.php?table=set_prediction&amp;name=rainforecast&amp;language=' + lang"/>
            <break time="1.0s"/>
            <!-- Toets 2 voor een regenseizoensvoorspelling. / Press 2 for a rainy season prediction. -->
            <audio expr="'<?php echo $siteurl; ?>/audio.php?table=set_prediction&amp;name=rainseason&amp;language=' + lang"/>
            <break time="1.0s"/>
            <!-- Toets 3 om een andere regio te kiezen. / Press 3 to choose another region. -->
            <audio expr="'<?php echo $siteurl; ?>/audio.php?table=set_prediction&amp;name=change_region&amp;language=' + lang"/>
            </prompt>

            <grammar type="application/x-nuance-gsl">
            [ dtmf-1 dtmf-2 dtmf-3 ]
            </grammar>

            <filled>
                <if cond="requested_pred_type==1">
                    <goto next="#set_rainprediction"/>
                <elseif cond="requested_pred_type==2"/>
                    <goto next="#rainy_season_prediction"/>
                <else/>
                    <goto next="#set_region"/>
                </if>
            </filled>
        </field>
    </form>

    <form id="set_rainprediction">
        <block name="explanation_header">
            <prompt>
                <!-- Regenvoorspellingen worden in 5 categorieen gegeven: / Rain predictions are given in 5 categories -->
                <audio expr="'<?php echo $siteurl; ?>/audio.php?table=set_rainprediction&amp;name=explanation_header&amp;language=' + lang"/>
                <break time="1.0s"/>
                <!-- Licht / light -->
                <audio expr="'<?php echo $siteurl; ?>/audio.php?table=prediction_categories&amp;name=light&amp;language=' + lang"/>
                <break time="0.5s"/>
                <!-- Gemiddeld / medium -->
                <audio expr="'<?php echo $siteurl; ?>/audio.php?table=prediction_categories&amp;name=medium&amp;language=' + lang"/>
                <break time="0.5s"/>
                <!-- Zwaar / heavy -->
                <audio expr="'<?php echo $siteurl; ?>/audio.php?table=prediction_categories&amp;name=heavy&amp;language=' + lang"/>
                <break time="0.5s"/>
                <!-- Erg zwaar / very heavy -->
                <audio expr="'<?php echo $siteurl; ?>/audio.php?table=prediction_categories&amp;name=very_heavy&amp;language=' + lang"/>
                <break time="0.5s"/>
                <!-- Extreme / Extreem -->
                <audio expr="'<?php echo $siteurl; ?>/audio.php?table=prediction_categories&amp;name=extreme&amp;language=' + lang"/>
            </prompt>
        </block>

        <field name="set_type">
            <prompt>
                <!-- Toets 1 voor een dagelijkse regenvoorspelling. / Press 1 for a daily rainprediction. -->
                <audio expr="'<?php echo $siteurl; ?>/audio.php?table=set_rainprediction&amp;name=daily_rainprediction&amp;language=' + lang"/>
                <break time="0.5s"/>
                <!-- Toets 2 voor een maandelijkse regenvoorspelling. / Press 2 for a monthly rainprediction. -->
                <audio expr="'<?php echo $siteurl; ?>/audio.php?table=set_rainprediction&amp;name=monthly_rainprediction&amp;language=' + lang"/>
            </prompt>

            <grammar type="application/x-nuance-gsl">
            [ dtmf-1 dtmf-2 ]
            </grammar>

            <filled>
                <if cond="set_type==1">
                    <goto next="#daily_rain_prediction"/>
                <elseif cond="set_type==2"/>
                    <goto next="#monthly_rain_prediction"/>
                </if>
            </filled>
        </field>
    </form>
    <form id="daily_rain_prediction">
        <block name="prediction">
            <prompt>
                <!-- Er wordt verwacht / Expected rain -->
                <audio expr="'<?php echo $siteurl; ?>/audio.php?table=rain_predictions&amp;name=expected&amp;language=' + lang"/>
                <break time="0.5s"/>

                <!-- Morgen / Tomorrow -->
                <audio expr="'<?php echo $siteurl; ?>/audio.php?table=rain_predictions&amp;name=tomorrow&amp;language=' + lang"/>
            </prompt>
                <?php
                    $day3_rain_predictions = array();

                    foreach ($regions as $region) {
                        $sql = "SELECT rain_tomorrow, rain_tdat, rain_tdat_tdat FROM u230489196_makeitrain.predictions WHERE region_name = '$region'";
                        $result_query = $conn->query($sql);
                        array_push($day3_rain_predictions, $result_query->fetch_assoc());
                    }

                    function output_day_rain_prediction($siteurl, $regions, $day3_rain_predictions, $day_offset) {
                        $first_region = $regions[0];
                        $cur_prediction = $day3_rain_predictions[0][$day_offset];
                        echo<<<FIRST_REGION
                        <if cond="region=='$first_region'">
                            <prompt>
                            <audio expr="'$siteurl/audio.php?table=prediction_categories&amp;name=$cur_prediction&amp;language=' + lang"/>
                            </prompt>\n
                        FIRST_REGION;

                        for ($i = 1; $i < count($regions); $i++) {
                            $cur_region = $regions[$i];
                            $cur_prediction = $day3_rain_predictions[$i][$day_offset];
                            echo<<<OTHER_REGIONS
                            <elseif cond="region=='$cur_region'"/>
                                <prompt>
                                <audio expr="'$siteurl/audio.php?table=prediction_categories&amp;name=$cur_prediction&amp;language=' + lang"/>
                                </prompt>\n
                            OTHER_REGIONS;
                        }

                        echo<<<END
                        </if>\n
                        END;
                    }

                    output_day_rain_prediction($siteurl, $regions, $day3_rain_predictions, "rain_tomorrow");
                 ?>
            <prompt>
                <break time="0.5s"/>

                <!-- Overmorgen / The day after tomorrow -->
                <audio expr="'<?php echo $siteurl; ?>/audio.php?table=rain_predictions&amp;name=tdat&amp;language=' + lang"/>
            </prompt>
                <?php
                    output_day_rain_prediction($siteurl, $regions, $day3_rain_predictions, "rain_tdat");
                 ?>
            <prompt>
                <break time="0.5s"/>
                <!-- Over 3 dagen / In 3 days -->
                <audio expr="'<?php echo $siteurl; ?>/audio.php?table=rain_predictions&amp;name=in_3days&amp;language=' + lang"/>
            </prompt>
            <?php
                output_day_rain_prediction($siteurl, $regions, $day3_rain_predictions, "rain_tdat_tdat");
             ?>
        </block>

        <field name="repeat_or_not">
            <prompt>
            <audio expr="'<?php echo $siteurl; ?>/audio.php?table=simple&amp;name=repeat_forecast&amp;language=' + lang"/>
            </prompt>

            <grammar type="application/x-nuance-gsl">
            [ dtmf-1 dtmf-2 ]
            </grammar>

            <filled>
                <if cond="repeat_or_not==1">
                    <clear/>
                    <goto nextitem="prediction"/>
                <else/>
                    <goto next="#end_call_or_terminate"/>
                </if>
            </filled>
        </field>
    </form>



    <form id="monthly_rain_prediction">
        <block name="prediction">
            <prompt>
                <!-- Er wordt verwacht / Expected rain -->
                <audio expr="'<?php echo $siteurl; ?>/audio.php?table=rain_predictions&amp;name=expected&amp;language=' + lang"/>
                <break time="0.5s"/>

                <!-- April / April-->
                <audio expr="'<?php echo $siteurl; ?>/audio.php?table=months&amp;name=april&amp;language=' + lang"/>
                <!-- Zwaar / Heavy -->
                <audio expr="'<?php echo $siteurl; ?>/audio.php?table=prediction_categories&amp;name=heavy&amp;language=' + lang"/>
                <break time="0.5s"/>

                <!-- Mei / May -->
                <audio expr="'<?php echo $siteurl; ?>/audio.php?table=months&amp;name=may&amp;language=' + lang"/>
                <!-- Gemiddeld / medium -->
                <audio expr="'<?php echo $siteurl; ?>/audio.php?table=prediction_categories&amp;name=medium&amp;language=' + lang"/>
                <break time="0.5s"/>

                <!-- Juni / June -->
                <audio expr="'<?php echo $siteurl; ?>/audio.php?table=months&amp;name=june&amp;language=' + lang"/>
                <!-- Licht / light -->
                <audio expr="'<?php echo $siteurl; ?>/audio.php?table=prediction_categories&amp;name=light&amp;language=' + lang"/>
            </prompt>
        </block>

        <field name="repeat_or_not">
            <prompt>
            <audio expr="'<?php echo $siteurl; ?>/audio.php?table=simple&amp;name=repeat_forecast&amp;language=' + lang"/>
            </prompt>

            <grammar type="application/x-nuance-gsl">
            [ dtmf-1 dtmf-2 ]
            </grammar>

            <filled>
                <if cond="repeat_or_not==1">
                    <clear/>
                    <goto nextitem="prediction"/>
                <else/>
                    <goto next="#end_call_or_terminate"/>
                </if>
            </filled>
        </field>
    </form>

    <form id="rainy_season_prediction">
        <block name="prediction">
            <?php
                $predictions = array();

                function output_prediction_prompt($siteurl, $cur_prediction) {
                    if ($cur_prediction <= 0) {
                        echo <<<RAIN_SEASON_STARTED
                        <prompt>
                            <audio expr="'$siteurl/audio.php?table=rainy_season_predictions&amp;name=$cur_prediction&amp;language=' + lang"/>
                        </prompt>\n
                        RAIN_SEASON_STARTED;
                    } else {
                        echo <<<RAIN_SEASON_WILL_START
                        <prompt>
                            <audio expr="'$siteurl/audio.php?table=rainy_season_predictions&amp;name=will_start_in&amp;language=' + lang"/>
                            <audio expr="'$siteurl/audio.php?table=numbers&amp;name=$cur_prediction&amp;language=' + lang"/>
                            <audio expr="'$siteurl/audio.php?table=rainy_season_predictions&amp;name=days&amp;language=' + lang"/>
                        </prompt>\n
                        RAIN_SEASON_WILL_START;
                    }
                }

                foreach ($regions as $region) {
                    $sql = "SELECT rainy_season_prediction FROM u230489196_makeitrain.predictions WHERE region_name = '$region'";
                    $result_query = $conn->query($sql);
                    array_push($predictions, intval($result_query->fetch_assoc()["rainy_season_prediction"]));
                }

                $first_region = $regions[0];
                echo <<<FIRST_REGION
                <if cond="region=='$first_region'">\n
                FIRST_REGION;
                output_prediction_prompt($siteurl, $predictions[0]);

                for ($i = 1; $i < count($predictions); $i++) {
                    $cur_region = $regions[$i];
                    echo <<<OTHER_REGIONS
                    <elseif cond="region=='$cur_region'"/>\n
                    OTHER_REGIONS;
                    output_prediction_prompt($siteurl, $predictions[$i]);
                }

             ?>
             </if>
        </block>

        <field name="repeat_or_not">
            <prompt>
            <audio expr="'<?php echo $siteurl; ?>/audio.php?table=simple&amp;name=repeat_forecast&amp;language=' + lang"/>
            </prompt>

            <grammar type="application/x-nuance-gsl">
            [ dtmf-1 dtmf-2 ]
            </grammar>

            <filled>
                <if cond="repeat_or_not==1">
                    <clear/>
                    <goto nextitem="prediction"/>
                <else/>
                    <goto next="#end_call_or_terminate"/>
                </if>
            </filled>
        </field>
    </form>

    <form id="end_call_or_terminate">
        <field name="choice">
            <prompt>
            <audio expr="'<?php echo $siteurl; ?>/audio.php?table=simple&amp;name=end_call_or_rechoose&amp;language=' + lang"/>
            </prompt>

            <grammar type="application/x-nuance-gsl">
            [ dtmf-1 dtmf-2 ]
            </grammar>

            <filled>
                <if cond="choice==1">
                    <goto next="#prediction_choose"/>
                <else/>
                    <prompt>
                    <audio expr="'<?php echo $siteurl; ?>/audio.php?table=simple&amp;name=outro&amp;language=' + lang"/>
                    </prompt>
                </if>
            </filled>
        </field>
    </form>
</vxml>
