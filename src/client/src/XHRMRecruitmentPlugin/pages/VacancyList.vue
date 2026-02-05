<!--
/**
 * XHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 XHRM Inc., http://www.XHRM.com
 *
 * XHRM is free software: you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * XHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with XHRM.
 * If not, see <https://www.gnu.org/licenses/>.
 */
 -->

<template>
  <div class="XHRM-container">
    <vacancy-card
      v-for="vacancy in vacancies?.data"
      :key="vacancy"
      :vacancy-id="vacancy.vacancyId"
      :vacancy-title="vacancy.vacancyTitle"
      :vacancy-description="vacancy.vacancyDescription"
    ></vacancy-card>
    <oxd-loading-spinner v-if="isLoading" class="XHRM-container-loader" />
    <div v-if="showPaginator" class="XHRM-bottom-container">
      <oxd-pagination v-model:current="currentPage" :length="pages" />
    </div>
  </div>
  <div class="XHRM-paper-container">
    <oxd-text tag="p" class="XHRM-vacancy-list-poweredby">
      {{ $t('recruitment.powered_by') }}
    </oxd-text>
    <img :src="defaultPic" alt="XHRM Picture" class="XHRM-container-img" />
    <slot name="footer"></slot>
  </div>
</template>

<script>
import VacancyCard from '@/XHRMRecruitmentPlugin/components/VacancyCard';
import {APIService} from '@/core/util/services/api.service';
import usePaginate from '@/core/util/composable/usePaginate';
import {OxdSpinner} from '@ohrm/oxd';

export default {
  name: 'VacancyList',
  components: {
    'vacancy-card': VacancyCard,
    'oxd-loading-spinner': OxdSpinner,
  },
  setup() {
    const defaultPic = `${window.appGlobal.publicPath}/images/ohrm_branding.png`;
    const vacancyDataNormalizer = (data) => {
      return data.map((item) => {
        return {
          vacancyId: item.id,
          vacancyTitle: item.name,
          vacancyDescription: item.description,
        };
      });
    };
    const http = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/recruitment/public/vacancies',
    );
    const {showPaginator, currentPage, total, pages, response, isLoading} =
      usePaginate(http, {
        normalizer: vacancyDataNormalizer,
        pageSize: 8,
      });
    return {
      defaultPic,
      showPaginator,
      currentPage,
      isLoading,
      total,
      pages,
      vacancies: response,
    };
  },
};
</script>

<style src="./public-job-vacancy.scss" lang="scss" scoped></style>
