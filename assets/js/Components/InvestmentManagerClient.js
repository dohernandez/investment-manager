'use strict';

class InvestmentManagerClient {
    /**
     * Send a remote procedure call to the server.
     *
     * @param url
     * @param method
     * @param formData
     * @return {Promise<any>}
     * @private
     */
    static sendRPC(url, method, formData = null) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url,
                method,
                data: formData ? JSON.stringify(formData) : ''
            }).then((data, textStatus, jqXHR) => {
                resolve(data);
            }).catch((jqXHR) => {
                if (jqXHR.status != 400 || jqXHR.status != 500) {
                    reject(jqXHR);

                    return;
                }

                const errorData = JSON.parse(jqXHR.responseText);

                reject(errorData);
            });
        });
    }
}

module.exports = InvestmentManagerClient;
