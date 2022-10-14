import axios from 'axios'

export function useUtils() {

    const ajaxRequest = (ajaxData, iteration = 0) => {

        return new Promise((resolve, reject) => {

            const options = {
                method: ajaxData.method,
                data: (ajaxData.params && ajaxData.method === "post") ? ajaxData.params : null,
                params: (ajaxData.params && ajaxData.method === "get") ? ajaxData.params : null,
                url: ajaxData.url
            };

            axios(options)
            .then(function (response) {
                resolve(response)
            })
            .catch(error => {

                if(error.response.status === 419 && iteration < 2){
                    iteration = iteration + 1
                    resolve(ajaxRequest(ajaxData, iteration))
                }else{
                    reject(error)
                }

            })

        })

    }

    return { ajaxRequest }

}
