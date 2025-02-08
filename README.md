# ShipStation API

## Instructions

Require the package in the `composer.json` file of your project, and map the package in the `repositories` section.
You must also map the `api-skeleton` package.

```json
{
    "require": {
        "php": ">=8.1",
        "anibalealvarezs/shipstation-api": "@dev"
    },
    "repositories": [
        {
          "type": "composer", "url": "https://satis.anibalalvarez.com/"
        }
    ]
}
```

Note: In order to use the package from GitLab, you need to have a valid SSH key configured in your GitLab account.

## Methods

- ### getOrders: *Array*

  `Gets the list of orders according to the specified page/pageSize.`

  <details>
    <summary><strong>Parameters</strong></summary>

    - Required

        - `page`: *Integer*  
            Page number to retrieve. Default is 1.
        - `pageSize`: *Integer*  
            Number of orders to retrieve per page. Default is 100.
  </details><br>

- ### getAllOrders: *Array*

  `Gets all orders.`

  <details>
    <summary><strong>Parameters</strong></summary>

    - Required

        - `loopLimit`: *Integer*  
            Number of loops to retrieve orders. Default is 10.
  </details><br>
