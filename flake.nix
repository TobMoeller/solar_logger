{
  description = "Solar Logger development environment";

  inputs.nixpkgs.url = "github:NixOS/nixpkgs/nixpkgs-unstable";

  outputs = { nixpkgs, ... }:
    let
      systems = [ "x86_64-linux" "aarch64-linux" ];
      forAllSystems = nixpkgs.lib.genAttrs systems;
    in
    {
      devShells = forAllSystems (system:
        let
          pkgs = import nixpkgs { inherit system; };
          php = pkgs.php85.buildEnv {
            extensions = { enabled, all }: enabled ++ [ all.redis ];
            extraConfig = ''
              session.upload_progress.enabled = 0
            '';
          };
        in
        assert pkgs.lib.versionAtLeast php.version "8.5.12";
        {
          default = pkgs.mkShell {
            packages = [
              php
              php.packages.composer
              pkgs.nodejs_24
              pkgs.python3
              pkgs.mariadb.client
              pkgs.redis
              pkgs.samba
              pkgs.unzip
            ];
          };
        });
    };
}
