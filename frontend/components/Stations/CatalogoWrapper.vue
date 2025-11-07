<template>
    <loading :loading="propsLoading" lazy>
        <catalogo-terraza v-if="props" v-bind="props"/>
    </loading>
</template>

<script setup lang="ts">
import {QueryKeys, queryKeyWithStation} from "~/entities/Queries.ts";
import {useAxios} from "~/vendor/axios.ts";
import {useQuery} from "@tanstack/vue-query";
import Loading from "~/components/Common/Loading.vue";
import {getStationApiUrl} from "~/router.ts";
import CatalogoTerraza from "~/components/Stations/CatalogoTerraza.vue";

const propsUrl = getStationApiUrl('/vue/catalogo');

const {axios} = useAxios();

interface CatalogoProps {
    playlists: Array<{
        id: number;
        name: string;
    }>;
    stationId: number;
    stationName: string;
}

const {data: props, isLoading: propsLoading} = useQuery<CatalogoProps>({
    queryKey: queryKeyWithStation(
        [
            QueryKeys.StationMedia,
            'catalogo-props'
        ]
    ),
    queryFn: async ({signal}) => {
        const {data} = await axios.get<CatalogoProps>(propsUrl.value, {signal});
        return data;
    }
});
</script>
