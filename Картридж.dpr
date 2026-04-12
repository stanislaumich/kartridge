program Картридж;

uses
  Vcl.Forms,
  UMain in 'UMain.pas' {FMAIN};

{$R *.res}

begin
  Application.Initialize;
  Application.MainFormOnTaskbar := True;
  Application.CreateForm(TFMAIN, FMAIN);
  Application.Run;
end.
