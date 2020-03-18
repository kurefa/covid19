<template>
  <div :class="$style.Advisory">
    <div :class="$style.AdvisoryContainer">
      <div :class="$style.AdvisoryContents">
        <div>
          <span :class="$style.AdvisoryContentsTitle">{{
            $t('新型コロナ受診相談窓口')
          }}</span>
        </div>
        <div :class="[$style.AdvisoryContentsColsSentense, 'mt-4']">
          {{ $t('帰国者・接触者相談センター') }}
        </div>
        <div :class="[$style.AdvisoryContentsColsSentense, 'mt-4']">
          {{ $t('（各地域の保健福祉事務所）') }}
        </div>
      </div>

      <div :class="$style.AdvisoryContents">
        <div class="py-8">
          <div :class="$style.AdvisoryContentsTitle2">
            {{ $t('平日（8:30～17:15）') }}
          </div>
          <div :class="$style.AdvisoryContentsTitle2">
            {{ $t('※ただし、緊急の場合は、夜間・土日も含め時間外も対応可能です。各保健福祉事務所に電話いただきますと、自動応答メッセージが流れますので、最後までお聞きいただき、その指示に従って対応してください。') }}
          </div>
          <div
            :class="[
              $style.AdvisoryLink,
              $style.AdvisoryBlockCentering,
              'mt-4'
            ]"
          >
            <a
              href="https://www.pref.saga.lg.jp/list00583.html"
              target="_blank"
              rel="noopener"
            >
              <span>{{ $t('各保健福祉事務所の電話番号は佐賀県健康・福祉HPへ') }}</span>
              <v-icon size="18">
                mdi-open-in-new
              </v-icon>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<i18n src="./FlowPcAdvisory.i18n.json"></i18n>

<style module lang="scss">
.Advisory {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  height: 100%;
  color: $gray-2;
  &Container {
    background-color: $gray-5;
    border-radius: 4px;
    height: 100%;
    padding: 30px 20px 20px 20px;
    margin-bottom: 10px;
    text-align: center;
  }
  &Contents {
    font-weight: bold;
    &:not(:first-child) {
      border-top: 0.5px solid $gray-4;
    }
    &Title {
      font-size: 26px;
      line-height: 28px;
    }
    &Title2 {
      font-size: 18px;
    }
    &SubTitle {
      font-size: 18px;
    }
    &ColsSentense {
      line-height: 18px;
    }
  }
  &BlockCentering {
    display: flex;
    justify-content: center;
  }
  &Link {
    line-height: 22px;
    text-align: left;
    a {
      color: rgba(0, 0, 0, 0.87);
      text-decoration: none;
      &:hover {
        text-decoration: underline;
      }
    }
  }
  &TelephoneArea {
    display: inline-flex;
  }
  &Telephone {
    display: flex;
    align-items: center;
    font-size: 27px;
    font-weight: bold;
    &:link,
    &:visited,
    &:hover,
    &:active,
    &:focus {
      color: inherit;
      text-decoration: none;
    }
    &::before {
      content: '';
      display: inline-block;
      margin-right: 5px;
      width: 26px;
      height: 26px;
      background-image: url(/flow/phone-24px.svg);
      background-repeat: no-repeat;
      background-size: contain;
    }
  }
  &BoxContainer {
    border-radius: 4px;
    text-align: center;
    padding: 20px 10px;
    margin: 24px auto;
  }
  &WhiteBox {
    background-color: $white;
    width: 160px;
    padding: 10px;
    &Sentense {
      color: $green-1;
      font-size: 18px;
      font-weight: bold;
    }
  }
}
</style>
