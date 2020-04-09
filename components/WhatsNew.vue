<template>
  <div class="WhatsNew">
    <h3 class="WhatsNew-heading">
      <v-icon size="24" class="WhatsNew-heading-icon">
        mdi-information
      </v-icon>
      {{ $t('最新のお知らせ') }}
    </h3>
    <v-select
      v-model="selectedItemLength"
      item-text="label"
      item-value="value"
      :items="itemLengths"
      label="表示件数"
      return-object
    />
    <ul class="WhatsNew-list">
      <li v-for="(item, i) in filterItems" :key="i" class="WhatsNew-list-item">
        <a
          class="WhatsNew-list-item-anchor"
          :href="item.url"
          target="_blank"
          rel="noopener"
        >
          <time
            class="WhatsNew-list-item-anchor-time px-2"
            :datetime="formattedDate(item.date)"
          >
            {{ item.date }}
          </time>
          <span class="WhatsNew-list-item-anchor-link">
            {{ $t(item.text) }}
            <v-icon
              v-if="!isInternalLink(item.url)"
              class="WhatsNew-item-ExternalLinkIcon"
              size="12"
            >
              mdi-open-in-new
            </v-icon>
          </span>
        </a>
      </li>
    </ul>
  </div>
</template>

<i18n src="./WhatsNew.i18n.json"></i18n>

<script>
import { convertDateToISO8601Format } from '@/utils/formatDate'

export default {
  props: {
    items: {
      type: Array,
      required: true
    }
  },
  data() {
    return {
      selectedItemLength: { label: '3件', value: 3 },
      itemLengths: [
        { label: '3件', value: 3 },
        { label: '5件', value: 5 },
        { label: '10件', value: 10 },
        { label: '全て', value: -1 }
      ]
    }
  },
  computed: {
    filterItems() {
      // 全て以外の場合は、指定された件数分だけ表示する。
      if (this.selectedItemLength.value !== -1) {
        return this.items.slice(0, this.selectedItemLength.value)
      }
      // 全ての場合はそのまま返す。
      return this.items
    }
  },
  methods: {
    isInternalLink(path) {
      return !/^https?:\/\//.test(path)
    },
    formattedDate(dateString) {
      return convertDateToISO8601Format(dateString)
    }
  }
}
</script>

<style lang="scss">
.WhatsNew {
  @include card-container();
  padding: 10px;
  margin-bottom: 20px;
}

.WhatsNew-heading {
  display: flex;
  align-items: center;
  @include card-h2();
  margin-bottom: 12px;
  color: $gray-2;
  margin-left: 12px;

  &-icon {
    margin: 3px;
  }
}

.WhatsNew .WhatsNew-list {
  padding-left: 0px;
  list-style-type: none;

  &-item {
    &-anchor {
      display: inline-flex;
      text-decoration: none;
      margin: 5px;
      font-size: 14px;

      @include lessThan($medium) {
        flex-wrap: wrap;
      }

      &-time {
        flex: 0 0 90px;
        @include lessThan($medium) {
          flex: 0 0 100%;
        }
        color: $gray-1;
      }

      &-link {
        flex: 0 1 auto;
        @include text-link();
        @include lessThan($medium) {
          padding-left: 8px;
        }
      }

      &-ExternalLinkIcon {
        margin-left: 2px;
        color: $gray-3 !important;
      }
    }
  }
}
</style>
